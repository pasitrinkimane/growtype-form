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
        add_filter('document_title_parts', array ($this, 'custom_document_title_parts'));

        add_filter('lostpassword_url', array ($this, 'lostpassword_url_rewrite'), 100, 2);
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
            add_rewrite_endpoint(self::URL_SLUG, EP_ROOT);
        }
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
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
        $form_args = growtype_form_extract_form_args($form_data);
        $wp_login_form_args = $form_args['wp_login_form'];

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
        <div class="growtype-form-wrapper" data-type="login">
            <div class="growtype-form-container">
                <?php
                if (isset($form_args['logo']) && isset($form_args['logo']['url']) && !empty($form_args['logo']['url'])) { ?>
                    <div class="logo-wrapper">
                        <a href="<?php echo isset($form_args['logo']['external_url']) ? growtype_form_string_replace_custom_variable($form_args['logo']['external_url']) : '#' ?>" class="e-logo">
                            <img src="<?php echo growtype_form_string_replace_custom_variable($form_args['logo']['url']) ?>" class="img-fluid" width="<?php echo $form_args['logo']['width'] ?? '' ?>" height="<?php echo $form_args['logo']['height'] ?? '' ?>"/>
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
                    <?php if ($form_args['header']) { ?>
                        <div class="growtype-form-header">
                            <?php if (isset($form_args['header']['top'])) { ?>
                                <div class="growtype-form-header-top">
                                    <?php if ($form_args['header']['top']['back_btn']) { ?>
                                        <a href="<?= isset($form_args['header']['top']['back_btn']['url']) ? growtype_form_string_replace_custom_variable($form_args['header']['top']['back_btn']['url']) : growtype_form_login_page_url() ?>" class="btn-back"></a>
                                    <?php } ?>
                                    <?php if (isset($form_args['header']['top']['title']) && !empty($form_args['header']['top']['title'])) { ?>
                                        <h2 class="e-title-intro"><?php echo $form_args['header']['top']['title'] ?></h2>
                                    <?php } ?>
                                    <?php if (isset($form_args['header']['top']['html']) && !empty($form_args['header']['top']['html'])) { ?>
                                        <div class="growtype-form-header-html">
                                            <?php echo growtype_form_string_replace_custom_variable($form_args['header']['top']['html']) ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <?php if (isset($form_args['header']['nav'])) { ?>
                                <ul class="nav">
                                    <?php foreach ($form_args['header']['nav'] as $nav) { ?>
                                        <li class="nav-item <?php echo $nav['class'] ?? '' ?>">
                                            <a href="<?php echo growtype_form_string_replace_custom_variable($nav['url']) ?>" class="nav-link"><?php echo $nav['label'] ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                            <?php if (isset($form_args['header']['bottom'])) { ?>
                                <div class="growtype-form-header-bottom">
                                    <?php if ($form_args['header']['bottom']['back_btn']) { ?>
                                        <a href="<?= isset($form_args['header']['bottom']['back_btn']['url']) ? growtype_form_string_replace_custom_variable($form_args['header']['bottom']['back_btn']['url']) : growtype_form_login_page_url() ?>" class="btn-back"></a>
                                    <?php } ?>
                                    <?php if (isset($form_args['header']['bottom']['title']) && !empty($form_args['header']['bottom']['title'])) { ?>
                                        <h2 class="e-title-intro"><?php echo $form_args['header']['bottom']['title'] ?></h2>
                                    <?php } ?>
                                    <div class="growtype-form-header-html">
                                        <?php echo growtype_form_string_replace_custom_variable($form_args['header']['bottom']['html']) ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <div class="form-inner-wrapper">
                        <?= wp_login_form($wp_login_form_args) ?>

                        <?php
                        if ($form_args['lost_password_btn']) { ?>
                            <a class="btn btn-link btn-recover-password" href="<?= growtype_form_lost_password_page_url() ?>"><?= isset($form_args['lost_password_label']) ? __($form_args['lost_password_label'], "growtype-form") : __("Lost your password?", "growtype-form") ?></a>
                        <?php } ?>
                    </div>

                    <?php if ($form_args['footer']) { ?>
                        <div class="growtype-form-footer">
                            <?php if (isset($form_args['footer']['top'])) { ?>
                                <div class="growtype-form-footer-top">
                                    <?php echo growtype_form_string_replace_custom_variable($form_args['footer']['top']['html']) ?>
                                </div>
                            <?php } ?>
                            <?php if (isset($form_args['footer']['nav'])) { ?>
                                <ul class="nav">
                                    <?php foreach ($form_args['footer']['nav'] as $nav) { ?>
                                        <li class="nav-item <?php echo $nav['class'] ?? '' ?>">
                                            <a href="<?php echo growtype_form_string_replace_custom_variable($nav['url']) ?>" class="nav-link"><?php echo $nav['label'] ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                            <?php if (isset($form_args['footer']['bottom'])) { ?>
                                <div class="growtype-form-footer-bottom">
                                    <?php echo growtype_form_string_replace_custom_variable($form_args['footer']['bottom']['html']) ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>

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

    /**
     * Redirect after login
     */
    function growtype_form_login_redirect()
    {
        return growtype_form_redirect_url_after_login();
    }
}
