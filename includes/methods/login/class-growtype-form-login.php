<?php

/**
 *
 */
class Growtype_Form_Login
{
    const URL_SLUG = 'login';

    public function __construct()
    {
        if (!is_admin()) {
            add_action('wp_login_failed', array ($this, 'custom_login_failed'), 10, 2);
            add_filter('authenticate', array ($this, 'custom_authenticate_username_password'), 30, 3);
            add_filter('login_redirect', array ($this, 'growtype_form_login_redirect'));
            add_filter('login_url', array ($this, 'change_default_login_url'), 10, 2);
        }

        add_action('init', array ($this, 'custom_url'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));
    }

    /**
     * @return void
     */
    function custom_url()
    {
        if (growtype_form_login_page_ID() === 'default') {
            add_rewrite_endpoint(self::URL_SLUG, EP_ALL);
        }
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $page_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

            if (growtype_form_login_page_is_active() && growtype_form_login_page_ID() === 'default') {
                echo growtype_form_include_view('login/default');
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

        if (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'wp/wp-admin')) {
            return wp_login_url();
        }

        if (!empty($referrer) && !empty(growtype_form_login_page_ID())) {
            if (isset($_GET['loggedout']) && !empty($_GET['loggedout'])) {
                return wp_redirect(add_query_arg('action', 'loggedout', growtype_form_login_page_url()));
            } else {
                return wp_redirect(add_query_arg('action', 'failed', growtype_form_login_page_url()));
            }
        }
    }

    /**
     * @param $form_data
     * @return false|string
     */
    public static function render_growtype_login_form($form_data)
    {
        $login_form_args = self::get_growtype_login_form_args($form_data);
        $wp_login_form_args = $login_form_args['wp_login_form'];

        $message = "";

        if (!empty($_GET['action'])) {
            if ('failed' == $_GET['action']) {
                $message = __("Wrong login details. Please try again.", "growtype-form");
            } elseif ('loggedout' == $_GET['action']) {
                $message = __("You are now logged out.", "growtype-form");
            } elseif ('recovered' == $_GET['action']) {
                $message = __("Check your e-mail for login information.", "growtype-form");
            }
        }

        ob_start();
        ?>
        <div class="growtype-form-wrapper">
            <div class="growtype-form-container">
                <?php
                if ($login_form_args['logo'] === true) { ?>
                    <div class="logo-wrapper">
                        <a href="<?= get_home_url() ?>" class="e-logo">
                            <img src="<?= get_login_logo()['url'] ?>" class="img-fluid"/>
                        </a>
                    </div>
                <?php } ?>

                <?php if ($message) { ?>
                    <?php if (isset($_GET['action']) && $_GET['action'] === 'failed') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $message ?>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $message ?>
                        </div>
                    <?php } ?>
                <?php } ?>

                <div class="form-wrapper">
                    <?php
                    if (!empty($login_form_args['title'])) { ?>
                        <h2 class="e-title-intro"><?= $login_form_args['title'] ?></h2>
                    <?php } ?>

                    <?= wp_login_form($wp_login_form_args) ?>

                    <?php
                    if ($login_form_args['lost_password_btn']) { ?>
                        <a class="btn btn-link btn-recover-password" href="<?= growtype_form_lost_password_page_url() ?>"><?= isset($login_form_args['lost_password_label']) ? __($login_form_args['lost_password_label'], "growtype-form") : __("Lost your password?", "growtype-form") ?></a>
                    <?php } ?>

                    <div class="b-actions">
                        <?php
                        if ($login_form_args['sign_up_btn']) { ?>
                            <p><?= __("You don’t have an account?", "growtype-form") ?></p>
                            <a class="btn btn-link" href="<?= growtype_form_signup_page_url() ?>"><?= __("Sign up", "growtype-form") ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        if (isset($login_form_args['username_placeholder'])) { ?>
            <script>
                var userLogin = document.getElementById("user_login");
                userLogin.setAttribute("placeholder", "<?= $login_form_args['username_placeholder'] ?>");
            </script>
        <?php } ?>

        <?php
        if (isset($login_form_args['password_placeholder'])) { ?>
            <script>
                var userPass = document.getElementById("user_pass");
                userPass.setAttribute("placeholder", "<?= $login_form_args['password_placeholder'] ?>");
            </script>
        <?php } ?>

        <?php

        return ob_get_clean();
    }

    /**
     * @param $form
     * @return array
     */
    static function get_growtype_login_form_args($form)
    {
        $title = $form['title'] ?? null;
        $logo = $form['logo'] ?? null;
        $sign_up_btn = $form['sign_up_btn'] ?? null;
        $lost_password_btn = $form['lost_password_btn'] ?? null;
        $lost_password_label = $form['lost_password_label'] ?? null;
        $username_placeholder = $form['username_placeholder'] ?? null;
        $password_placeholder = $form['password_placeholder'] ?? null;

        $wp_login_form = $form['wp_login_form'][0] ?? null;
        $redirect = isset($wp_login_form['redirect']) && !empty($wp_login_form['redirect']) ? $wp_login_form['redirect'] : admin_url();
        $wp_login_form_id = isset($wp_login_form['form_id']) && !empty($wp_login_form['form_id']) ? $wp_login_form['form_id'] : 'loginform-custom';
        $label_username = isset($wp_login_form['label_username']) && !empty($wp_login_form['label_username']) ? $wp_login_form['label_username'] : __('Username', "growtype-form");
        $label_password = isset($wp_login_form['label_password']) && !empty($wp_login_form['label_password']) ? $wp_login_form['label_password'] : __('Password', "growtype-form");
        $label_remember = isset($wp_login_form['label_remember']) && !empty($wp_login_form['label_remember']) ? $wp_login_form['label_remember'] : __('Remember Me', "growtype-form");
        $label_log_in = isset($wp_login_form['label_log_in']) && !empty($wp_login_form['label_log_in']) ? $wp_login_form['label_log_in'] : __('Log In', "growtype-form");
        $remember = isset($wp_login_form['remember']) && !empty($wp_login_form['remember']) ? $wp_login_form['remember'] : true;

        return [
            'title' => $title,
            'logo' => $logo,
            'sign_up_btn' => $sign_up_btn,
            'lost_password_btn' => $lost_password_btn,
            'lost_password_label' => $lost_password_label,
            'username_placeholder' => $username_placeholder,
            'password_placeholder' => $password_placeholder,
            'wp_login_form' => [
                'redirect' => $redirect,
                'form_id' => $wp_login_form_id,
                'label_username' => $label_username,
                'label_password' => $label_password,
                'label_remember' => $label_remember,
                'label_log_in' => $label_log_in,
                'remember' => $remember,
                'placeholder_username' => __('Your username...'),
                'placeholder_password' => __('Your password...')
            ]
        ];
    }

    /**
     * Redirect after login
     */
    function growtype_form_login_redirect()
    {
        return growtype_form_redirect_url_after_login();
    }
}
