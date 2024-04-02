<?php

/**
 *
 */
class Growtype_Form_Google
{
    use GrowtypeFormNotice;

    public function __construct()
    {
        $this->client_id = get_option('growtype_form_settings_credentials_google_client_id');
        $this->client_secret = get_option('growtype_form_settings_credentials_google_client_secret');

        add_action('init', array ($this, 'validate_response'));
    }

    public function init_client()
    {
        $clientID = $this->client_id;
        $clientSecret = $this->client_secret;
        $redirectUri = growtype_form_current_page_is_login_page() ? growtype_form_login_page_url() : growtype_form_signup_page_url();

        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        /**
         * Same extra params to state
         */
        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $form_origin_url = strtok($_SERVER['REQUEST_URI'], '?');
            $form_origin_url = home_url($form_origin_url);

            $params = $this->base64UrlEncode(json_encode([
                'form_origin_url' => $form_origin_url,
            ]));

            $client->setState($params);
        }

        return $client;
    }

    public function base64UrlEncode($inputStr)
    {
        return strtr(base64_encode($inputStr), '+/=', '-_,');
    }

    public function base64UrlDecode($inputStr)
    {
        return base64_decode(strtr($inputStr, '-_,', '+/='));
    }

    public function login_url()
    {
        try {
            if (!isset($_GET['code'])) {
                $client = $this->init_client();

                return $client->createAuthUrl();
            }
        } catch (Exception $e) {
            error_log(sprintf('Google login error: %s', $e->getMessage()));
        }
    }

    function validate_response()
    {
        if (isset($_GET['code'])) {
            $client = $this->init_client();

            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (isset($token['access_token'])) {
                $client->setAccessToken($token['access_token']);

                $google_oauth = new Google_Service_Oauth2($client);
                $google_account_info = $google_oauth->userinfo->get();

                $email = $google_account_info->email;
                $full_name = $google_account_info->name;
                $first_name = $google_account_info->givenName;
                $last_name = $google_account_info->familyName;
                $gender = $google_account_info->gender;
                $picture = $google_account_info->picture;
                $locale = $google_account_info->locale;
                $google_id = $google_account_info->id;
                $link = $google_account_info->link;

                $state = isset($_GET['state']) && !empty($_GET['state']) ? $_GET['state'] : '';
                $form_origin_url = '';

                if (!empty($state)) {
                    $state = base64_decode(strtr($state, '-_,', '+/='));
                    $state = json_decode($state, true);

                    if (isset($state['form_origin_url'])) {
                        $form_origin_url = $state['form_origin_url'];
                    }
                }

                $user_id = email_exists($email);

                /**
                 * If user does not exist, create one
                 */
                if (!$user_id) {
                    $create_user = Growtype_Form_Signup::create_user($email, wp_generate_password(22, true), $email);

                    if (empty($create_user) || $create_user['success'] === false) {
                        error_log(sprintf('Google login error. Could not create user. Account info %s', print_r($google_account_info, true)));

                        $parts = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : [];
                        $query_args = isset($parts['query']) ? parse_str($parts['query'], $query_args) : [];

                        $this->growtype_form_set_notice([
                            __("Something went wrong. Please try again or contact our support.", "growtype-form")
                        ], 'error');

                        $redirect_url = growtype_form_signup_page_url();

                        if (growtype_form_current_page_is_login_page()) {
                            $redirect_url = growtype_form_login_page_url();
                        }

                        if (!empty($form_origin_url)) {
                            $redirect_url = $form_origin_url;
                        }

                        wp_redirect($redirect_url);
                        exit();
                    }

                    $user_id = $create_user['user_id'];

                    $growtype_form_crud = new Growtype_Form_Crud();

                    $growtype_form_crud->update_user_meta_details($user_id, [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'gender' => $gender,
                        'profile_picture' => $picture,
                        'google_locale' => $locale,
                        'google_id' => $google_id,
                        'google_account_link' => $link,
                        'google_full_name' => $full_name,
                    ]);
                }

                growtype_form_login_user($user_id);

                do_action('growtype_form_google_auth', [
                    'user_id' => $user_id,
                    'google_account_info' => $google_account_info,
                    'form_origin_url' => $form_origin_url
                ]);

                $redirect_url = apply_filters('growtype_form_google_auth_redirect_url', growtype_form_redirect_url_after_signup(), $form_origin_url);

                if (!empty($redirect_url)) {
                    wp_redirect($redirect_url);
                    exit();
                }
            } else {
                error_log(sprintf('Google login error: %s', $token['error']));

                wp_redirect(growtype_form_signup_page_url());
                exit();
            }
        }
    }
}
