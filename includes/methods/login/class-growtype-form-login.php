<?php

/**
 *
 */
class Growtype_Form_Login
{
    use GrowtypeFormNotice;

    const URL_PATH = 'login';

    public function __construct()
    {
        if (!is_admin()) {
            add_action('wp_login_failed', array ($this, 'custom_login_failed'), 10, 2);
//            add_filter('authenticate', array ($this, 'custom_authenticate_username_password'), 30, 3);
            add_filter('login_url', array ($this, 'change_default_login_url'), 10, 2);
        }

        add_action('init', array ($this, 'custom_url'), 1);

        add_action('template_redirect', array ($this, 'custom_url_template'));

        add_filter('document_title_parts', array ($this, 'custom_document_title_parts'));

        add_filter('lostpassword_url', array ($this, 'lostpassword_url_rewrite'), 100, 2);

        add_filter('nav_menu_css_class', array ($this, 'nav_menu_css_class'), 100, 2);

        add_filter("retrieve_password_notification_email", array ($this, 'retrieve_password_notification_email_callback'), 99, 4);

        /**
         * Password reset
         */
        add_filter('retrieve_password_message', array ($this, 'retrieve_password_message_callback'), 10, 4);

        /**
         * Login form
         */
        add_filter('login_form_middle', array ($this, 'login_form_middle_custom'), 0, 2);
    }

    function login_form_middle_custom($html, $args)
    {
        $html .= '<a class="btn btn-link btn-recover-password" href="' . growtype_form_lost_password_page_url() . '">' . __("Lost your password?", "growtype-form") . '</a>';

        return $html;
    }

    /**
     * @param $message
     * @param $key
     * @param $user_login
     * @param $user_data
     * @return string
     * Password reset message
     */
    function retrieve_password_message_callback($message, $key, $user_login, $user_data)
    {
        $locale = get_user_locale($user_data);
        $password_reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . '&wp_lang=' . $locale . "\r\n\r\n";

        $message = __("It seems that you've requested a password reset for your account.", "growtype-form") . "\r\n\r\n";
        $message .= __("Click on the following link to initiate the password reset process:", "growtype-form") . " " . $password_reset_url . "\r\n\r\n";
        $message .= __("Make sure that your new password is strong and unique for your account's security.", "growtype-form") . "\r\n\r\n";
        $message .= sprintf(__("If you didn't request this password reset, or if you have any questions about the security of your account, please contact our support at %s.", 'growtype-form'), get_option('admin_email')) . "\r\n\r\n";

        return $message;
    }

    function retrieve_password_notification_email_callback($defaults, $key, $user_login, $user_data)
    {
        $use_altervative_email = get_user_meta($user_data->ID, 'use_alternative_email', true);

        if (!empty($use_altervative_email) && $use_altervative_email) {
            $alternative_email = get_user_meta($user_data->ID, 'email', true);

            if (!empty($alternative_email)) {
                $defaults['to'] = $alternative_email;
            }
        }

        return $defaults;
    }

    function nav_menu_css_class($classes, $menu_item)
    {
        global $wp;

        if (strpos($menu_item->url, 'login') !== false || strpos($menu_item->url, 'signup') !== false) {
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

            if (strpos($request_uri, 'redirect_after') === false) {
                $parts = parse_url($menu_item->url);
                $query_args = isset($parts['query']) ? parse_str($parts['query'], $query_args) : [];

                $permalink = !empty(get_permalink()) ? get_permalink() : home_url($wp->request);

                if (!empty($permalink)) {
                    array_push($query_args, ['redirect_after' => $permalink]);

                    $menu_item->url = $menu_item->url . '?' . build_query($query_args);
                }
            }
        }

        return $classes;
    }

    function lostpassword_url_rewrite($lostpassword_url, $redirect)
    {
        if (class_exists('woocommerce') && !is_user_logged_in()) {
            return network_site_url('wp-login.php?action=lostpassword', 'login');
        }

        return $lostpassword_url;
    }

    /**
     * @param $title
     * @return string
     */
    function custom_document_title_parts($title_parts)
    {
        if (growtype_form_login_page_is_active() && growtype_form_login_page_ID() === 'default') {
            $title_parts['title'] = __('Sign in', 'growtype-form');
        }

        return $title_parts;
    }

    /**
     * @return void
     */
    function custom_url()
    {
        if (growtype_form_login_page_ID() === 'default') {
            add_rewrite_endpoint(self::URL_PATH, EP_ROOT);
        }
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            if (growtype_form_login_page_is_active() && growtype_form_login_page_ID() === 'default') {
                if (is_user_logged_in()) {
                    echo growtype_form_include_view('login.success');
                } else {
                    echo growtype_form_include_view('login.default');
                }
                exit;
            }
        }
    }

    /**
     * Change the login url to the custom login page
     */
    function change_default_login_url($login_url = '', $redirect = '')
    {
        if (isset($_GET["action"]) && $_GET["action"] === 'lostpassword') {
            $login_url = growtype_form_login_page_url();
        }

        return $login_url;
    }

    /**
     * Updates authentication to return an error when one field or both are blank
     */
    function custom_authenticate_username_password($user, $username, $password)
    {
        if (is_a($user, 'WP_User')) {
            return $user;
        }

        if (empty($username) || empty($password)) {
            $error = new WP_Error();

            return $error;
        }
    }

    /**
     * Updates login failed to send user back to the custom form with a query var
     */
    function custom_login_failed($username)
    {
        $referrer = wp_get_referer();

        /**
         * Check if the referrer is the wp-admin login page
         */
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wp/wp-admin') !== false) {
            return wp_login_url();
        }

        /**
         * Check if the referrer is the custom login page
         */
        if (!empty($referrer) && !empty(growtype_form_login_page_ID())) {
            $query_string = parse_url($referrer, PHP_URL_QUERY);
            parse_str($query_string, $query_args);

            if (isset($_GET['loggedout']) && !empty($_GET['loggedout'])) {
                $query_args['action'] = 'loggedout';
            } else {
                $query_args['action'] = 'failed';
            }

            $redirect_url = $referrer;

            return wp_redirect(add_query_arg($query_args, $redirect_url));
        }

        return wp_redirect(growtype_form_login_page_url());
    }

    /**
     * @param $form_data
     * @return false|string
     */
    public static function render_growtype_login_form($form_data)
    {
        $form_args = growtype_form_extract_form_args($form_data);
        $wp_login_form_args = $form_args['wp_login_form'];

        $wp_login_form_args['redirect'] = growtype_form_redirect_url_after_login();

        /**
         * Check if redirect after parameter exists
         */
        if (isset($_GET['redirect_after']) && !empty($_GET['redirect_after']) && strpos($_GET['redirect_after'], get_bloginfo('url')) > -1) {
            $wp_login_form_args['redirect'] = $_GET['redirect_after'];
        } elseif (isset($form_data['args']['redirect_after']) && !empty($form_data['args']['redirect_after'])) {
            $wp_login_form_args['redirect'] = $form_data['args']['redirect_after'];
        }

        /**
         * Classes
         */
        $form_class = isset($form_data['args']['class']) ? $form_data['args']['class'] : '';

        /**
         * Render form
         */
        ob_start();
        ?>

        <div class="growtype-form-wrapper <?= $form_class ?>" data-name="login">
            <div class="growtype-form-container">
                <?php if (isset($form_args['logo']) && isset($form_args['logo']['url']) && !empty($form_args['logo']['url'])) { ?>
                    <div class="logo-wrapper">
                        <a href="<?php echo isset($form_args['logo']['external_url']) ? growtype_form_string_replace_custom_variable($form_args['logo']['external_url']) : '#' ?>" class="e-logo">
                            <img src="<?php echo growtype_form_string_replace_custom_variable($form_args['logo']['url']) ?>" class="img-fluid" width="<?php echo $form_args['logo']['width'] ?? '' ?>" height="<?php echo $form_args['logo']['height'] ?? '' ?>"/>
                        </a>
                    </div>
                <?php } ?>

                <div class="form-wrapper">
                    <?php echo growtype_form_include_view('components.forms.partials.header', ['form_args' => $form_args]) ?>

                    <div class="form-inner-wrapper">
                        <?= wp_login_form($wp_login_form_args) ?>
                    </div>

                    <?php echo growtype_form_include_view('components.forms.partials.footer', ['form_args' => $form_args]) ?>
                </div>
            </div>
        </div>

        <?php
        if (isset($form_args['username_placeholder'])) { ?>
            <script>
                var userLogin = document.getElementById("user_login");
                userLogin.setAttribute("placeholder", "<?= $form_args['username_placeholder'] ?>");
            </script>
        <?php } ?>

        <?php
        if (isset($form_args['password_placeholder'])) { ?>
            <script>
                var userPass = document.getElementById("user_pass");
                userPass.setAttribute("placeholder", "<?= $form_args['password_placeholder'] ?>");
            </script>
        <?php } ?>

        <script>
            document.querySelector('#user_login').setAttribute('required', 'required');
            document.querySelector('#user_pass').setAttribute('required', 'required');
        </script>

        <?php

        return ob_get_clean();
    }
}
