<?php

/**
 *
 */
class Growtype_Form_Signup_Verification
{
    const EMAIL_VERIFICATION_FORM_NAME = 'email_verification';
    const SEND_VERIFICATION_CODE_KEY = 'send_verification_code';
    const USER_VERIFICATION_SUCCESS_KEY = 'is_verified';

    const EMAIL_RESEND_TIME_LIMIT = 30;

    public function __construct()
    {
        if (self::email_verification_is_required() && !is_admin()) {
            add_action('init', array ($this, 'handle_email_verification'));

            /**
             *
             */
            add_action('parse_request', array ($this, 'email_verification_page'));

            /**
             *
             */
            add_action('init', array ($this, 'resend_verification_email'));

            /**
             *
             */
            add_action('init', array ($this, 'check_if_email_is_verified'));

            /**
             *
             */
            add_action('wp_loaded', array ($this, 'render_notifications'));

            /**
             *
             */
            add_action('wp_head', array ($this, 'custom_page_css'));

            /**
             *
             */
            add_action('growtype_form_create_user', array ($this, 'send_initial_verification_email'));
        }
    }

    public static function email_verification_is_required()
    {
        return filter_var(get_option('growtype_form_signup_requires_email_confirmation'), FILTER_VALIDATE_BOOLEAN);
    }

    function custom_page_css()
    {
        if (self::is_email_verification_page()) { ?>
            <style>
                .verificationinfo {
                    max-width: 480px;
                    margin: auto;
                    text-align: center;
                    background: white;
                    color: black;
                    padding: 20px;
                    border-radius: 5px;
                }

                .verificationinfo .verificationinfo-inner {
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }
            </style>
            <?php
        }
    }

    public static function is_logout_action()
    {
        return isset($_GET['action']) && $_GET['action'] === 'logout';
    }

    public static function is_delete_account_action()
    {
        return (isset($_GET['action']) && $_GET['action'] === 'delete_account') || (isset($_GET['wc-api']) && $_GET['wc-api'] === 'wc-delete-account');
    }

    function check_if_email_is_verified()
    {
        $apply_redirect = true;

        if (self::is_logout_action()) {
            $apply_redirect = false;
        }

        if (self::is_delete_account_action()) {
            $apply_redirect = false;
        }

        if (class_exists('Growtype_Wc') && growtype_wc_is_account_page()) {
            $apply_redirect = false;
        }

        if ($apply_redirect) {
            if (!is_user_logged_in() && self::is_email_verification_page()) {
                wp_redirect(home_url());
                exit;
            }

            if (is_user_logged_in() && self::is_email_verification_page() && self::is_user_verified()) {
                wp_redirect(home_url());
                exit;
            }

            if (is_user_logged_in() && !self::is_user_verified() && !self::is_email_verification_page()) {
                wp_redirect(home_url(self::verification_page_slug()));
                exit;
            }
        }
    }

    function render_notifications()
    {
        if (isset($_COOKIE['growtype_form_successful_verification_notification'])) {
            setcookie('growtype_form_successful_verification_notification', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);

            add_action('wp_head', function () {
                ?>
                <style>
                    #alert-verification {
                        position: absolute;
                        z-index: 100;
                        top: 40px;
                        left: 15px;
                        right: 15px;
                        margin: auto;
                        max-width: 580px;
                    }
                </style>
                <script>
                    setTimeout(function () {
                        document.getElementById('alert-verification').style.display = 'none';
                    }, 4000);
                </script>
                <?php
            });

            add_action('wp_body_open', function () {
                ?>
                <div id="alert-verification" class="alert alert-success" role="alert">
                    <?php esc_html_e('Thank you, your account was verified successfully.', 'growtype-form'); ?>
                </div>
                <?php
            });
        }
    }

    public static function is_user_verified($user_id = null)
    {
        $user_id = !empty($user_id) ? $user_id : get_current_user_id();

        $user = get_userdata($user_id);

        if (!$user) {
            return true;
        }

        $verification_needed_for_roles = apply_filters('growtype_form_verification_needed_for_roles', ['subscriber', 'customer', 'lead']);

        $verification_is_needed = false;
        foreach ($verification_needed_for_roles as $role) {
            if (in_array($role, $user->roles)) {
                $verification_is_needed = true;
            }
        }

        if (!$verification_is_needed) {
            return true;
        }

        $is_verified = get_user_meta($user_id, self::USER_VERIFICATION_SUCCESS_KEY, true);

        return apply_filters('growtype_form_is_user_verified', $is_verified, $user_id);
    }

    public static function verification_page_slug()
    {
        return '/gf/email-verification';
    }

    public static function get_current_user_email()
    {
        $current_user = wp_get_current_user();

        if ($current_user->exists()) {
            return $current_user->user_email;
        }

        return null;
    }

    function email_verification_page()
    {
        if (is_user_logged_in() && self::is_email_verification_page()) {
            $intro_text_intro = __("<h3>You're almost there!</h3>", 'growtype-form');

            $intro_text_main = sprintf(__("<p>We've just sent a verification email to <b>%s</b>.</p> <p>To unlock all the great features, simply check your inbox and follow the quick instructions to verify your account.</p>", 'growtype-form'), self::get_current_user_email());

            if (isset($_GET['resend']) && filter_var($_GET['resend'], FILTER_VALIDATE_BOOLEAN)) {
                $intro_text_main = sprintf(__('<p>A new verification email has been sent to <b>%s</b>.</p> <p>Please check your email and follow the instructions to verify your account.</p>', 'growtype-form'), self::get_current_user_email());
            } elseif (isset($_GET['resend']) && !filter_var($_GET['resend'], FILTER_VALIDATE_BOOLEAN)) {
                $intro_text_main = sprintf(__('<p>Please wait at least %s seconds before resending your verification email.</p>', 'growtype-form'), self::EMAIL_RESEND_TIME_LIMIT);
            } elseif (isset($_GET['verified']) && !filter_var($_GET['verified'], FILTER_VALIDATE_BOOLEAN)) {
                $intro_text_main = __('<p>The verification link is invalid or expired. Please try again.</p>', 'growtype-form');
            } elseif (isset($_GET['verified']) && filter_var($_GET['verified'], FILTER_VALIDATE_BOOLEAN)) {
                $intro_text_main = __('<p>Your email has been verified.</p>', 'growtype-form');
            }

            $intro_text = $intro_text_intro . $intro_text_main;

            echo growtype_form_include_view('verification.email', [
                'form_name' => self::EMAIL_VERIFICATION_FORM_NAME,
                'intro_text' => $intro_text,
                'action' => home_url(self::verification_page_slug()),
                'current_user_email' => self::get_current_user_email(),
                'send_verification_code_key' => self::SEND_VERIFICATION_CODE_KEY,
                'admin_email' => env('ADMIN_EMAIL'),
            ]);

            exit();
        }
    }

    /**
     * @return bool
     */
    public static function is_email_verification_page()
    {
        $is_chat_page = false;
        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $url_parts = isset(parse_url($_SERVER['REQUEST_URI'])['path']) ? array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'])['path'])) : [];
            $url_parts_formatted = implode('/', $url_parts);

            $verification_page_slug_parts = array_filter(explode('/', self::verification_page_slug()));
            $verification_page_slug_parts_formatted = implode('/', $verification_page_slug_parts);

            if ($url_parts_formatted === $verification_page_slug_parts_formatted) {
                $is_chat_page = true;
            }
        }

        return $is_chat_page;
    }

    function send_initial_verification_email($user_id)
    {
        if (!self::is_user_verified($user_id)) {
            $activation_key = self::generate_user_activation_key($user_id);

            $user = get_userdata($user_id);

            wp_update_user([
                'ID' => $user_id,
                'user_activation_key' => $activation_key
            ]);

            self::send_verification_email($user->user_email, $activation_key);

            error_log(sprintf('Growtype Form - Initial verification email was sent to: %s', $user->user_email));
        }
    }

    public static function send_verification_email($email, $activation_key)
    {
        $verification_link = add_query_arg(
            ['key' => $activation_key],
            home_url(self::verification_page_slug())
        );

        $subject = __("Please Verify Your Account", 'growtype-form');

        // HTML message with a "Verify my account" link
        $message = sprintf(
            __(
                'Dear User,<br><br>
            Thank you for registering with our service. To complete your registration and verify your account, please click the button below:<br><br>
            <a href="%s" style="display: inline-block; padding: 5px 20px; color: #fff; background-color: %s; text-decoration: none; border-radius: 5px;">Verify my account</a><br><br>
            If you did not register for an account, please disregard this email.<br><br>
            Best regards,<br>
            Support Team',
                'growtype-form'
            ),
            esc_url($verification_link),
            growtype_theme_color()
        );

        // Set the content type to HTML
        add_filter('wp_mail_content_type', function () {
            return 'text/html';
        });

        // Send the email
        wp_mail($email, $subject, $message);

        // Reset content type to avoid affecting other emails
        remove_filter('wp_mail_content_type', function () {
            return 'text/html';
        });

        error_log(sprintf('Growtype Form - Verification link %s was sent to %s.', $verification_link, $email));
    }

    function handle_email_verification()
    {
        if (is_user_logged_in() && !self::is_user_verified() && self::is_email_verification_page() && isset($_GET['key'])) {
            global $wpdb;

            $activation_key = sanitize_text_field($_GET['key']);

            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM $wpdb->users WHERE user_activation_key = %s",
                $activation_key
            ));

            if (!empty($user_id)) {
                $user_info = get_userdata($user_id);

                if (empty($user_info)) {
                    error_log(sprintf('Growtype Form - User not found for activation key %s.', $activation_key));
                    wp_die(__('User not found for activation key.', 'growtype-form'));
                }

                $user_email = $user_info->user_email;

                update_user_meta($user_id, self::USER_VERIFICATION_SUCCESS_KEY, 1);

                if (!isset($_COOKIE['growtype_form_successful_verification_notification'])) {
                    setcookie('growtype_form_successful_verification_notification', 1, time() + (86400 * 7), COOKIEPATH, COOKIE_DOMAIN);
                }

                do_action('growtype_form_successful_user_verification', $user_id);

                error_log(sprintf('Growtype Form - User Email %s verification is successful!', $user_email));

                $redirect_url = apply_filters('growtype_form_successful_user_verification_redirect_url', home_url());

                wp_redirect($redirect_url);
                exit;
            } else {
                $redirect_url = add_query_arg(['verified' => 'false'], home_url(self::verification_page_slug()));

                wp_redirect($redirect_url);
                exit;
            }
        }
    }

    public static function resend_verification_email()
    {
        if (isset($_POST[self::SEND_VERIFICATION_CODE_KEY]) && filter_var($_POST[self::SEND_VERIFICATION_CODE_KEY], FILTER_VALIDATE_BOOLEAN) && !self::is_user_verified()) {
            $user_id = get_current_user_id();

            $last_resent_time = get_user_meta($user_id, 'last_verification_resent_time', true);

            $current_time = time();

            if ($last_resent_time && ($current_time - $last_resent_time) < self::EMAIL_RESEND_TIME_LIMIT) {
                $redirect_url = add_query_arg(['resend' => 'false'], home_url(self::verification_page_slug()));
                wp_redirect($redirect_url);
                exit;
            }

            $activation_key = self::generate_user_activation_key($user_id);
            $user = get_userdata($user_id);
            self::send_verification_email($user->user_email, $activation_key);

            update_user_meta($user_id, 'last_verification_resent_time', $current_time);

            error_log(sprintf('Growtype Form - Verification email was resent to: %s', $user->user_email));

            $redirect_url = add_query_arg(['resend' => 'true'], home_url(self::verification_page_slug()));
            wp_redirect($redirect_url);
            exit;
        }
    }

    public static function generate_user_activation_key($user_id)
    {
        $activation_key = wp_generate_password(40, false);

        wp_update_user([
            'ID' => $user_id,
            'user_activation_key' => $activation_key
        ]);

        return $activation_key;
    }
}
