<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/partials
 */

trait FrontendLogin
{
    /**
     * @param $form_data
     * @return false|string
     */
    function render_growtype_login_form($form_data)
    {
        $args = $this->get_growtype_login_form_args($form_data);
        $wp_login_form_args = $args['wp_login_form'];

        $message = "";

        if (!empty($_GET['action'])) {
            if ('failed' == $_GET['action']) {
                $message = __("Wrong login details. Please try again.", "growtype-registration");
            } elseif ('loggedout' == $_GET['action']) {
                $message = __("You are now logged out.", "growtype-registration");
            } elseif ('recovered' == $_GET['action']) {
                $message = __("Check your e-mail for login information.", "growtype-registration");
            }
        }

        ob_start();
        ?>
        <div class="growtype-form-wrapper">
            <div class="growtype-form-container">
                <?php
                if ($args['logo'] === true) { ?>
                    <div class="logo-wrapper">
                        <a href="<?= get_home_url() ?>" class="e-logo">
                            <img src="<?= get_login_logo()['url'] ?>" class="img-fluid"/>
                        </a>
                    </div>
                <?php } ?>

                <?php if ($message) { ?>
                    <?php if (isset($_GET['action']) && $_GET['action'] === 'failed') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?= __($message, 'growtype-registration') ?>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-success" role="alert">
                            <?= __($message, 'growtype-registration') ?>
                        </div>
                    <?php } ?>
                <?php } ?>

                <div class="form-wrapper">
                    <?php
                    if (!empty($args['title'])) { ?>
                        <h2 class="e-title-intro"><?= $args['title'] ?></h2>
                    <?php } ?>

                    <?= wp_login_form($wp_login_form_args) ?>

                    <?php
                    if ($args['lost_password_btn']) { ?>
                        <a class="btn btn-link btn-recover-password" href="<?= growtype_form_lostpassword_url() ?>"><?= __("Lost your password?", "growtype-registration") ?></a>
                    <?php } ?>

                    <div class="b-actions">
                        <?php
                        if ($args['sign_up_btn']) { ?>
                            <label for=""><?= __("You don’t have an account?", "growtype-registration") ?></label>
                            <a class="btn btn-link" href="<?= growtype_form_signup_url() ?>"><?= __("Sign up", "growtype-registration") ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php

        return ob_get_clean();
    }

    /**
     * @param $form
     * @return array
     */
    function get_growtype_login_form_args($form)
    {
        $title = $form['title'] ?? null;
        $logo = $form['logo'] ?? null;
        $sign_up_btn = $form['sign_up_btn'] ?? null;
        $lost_password_btn = $form['lost_password_btn'] ?? null;
        $redirect = isset($form['redirect']) && !empty($form['redirect']) ? $form['redirect'] : admin_url();
        $form_id = isset($form['form_id']) && !empty($form['form_id']) ? $form['form_id'] : 'loginform-custom';
        $label_username = isset($form['label_username']) && !empty($form['label_username']) ? $form['label_username'] : __('Username', "growtype-form");
        $label_password = isset($form['label_password']) && !empty($form['label_password']) ? $form['label_password'] : __('Password', "growtype-form");
        $label_remember = isset($form['label_remember']) && !empty($form['label_remember']) ? $form['label_remember'] : __('Remember Me', "growtype-form");
        $label_log_in = isset($form['label_log_in']) && !empty($form['label_log_in']) ? $form['label_log_in'] : __('Log In', "growtype-form");
        $remember = isset($form['remember']) && !empty($form['remember']) ? $form['remember'] : true;

        return [
            'title' => $title,
            'logo' => $logo,
            'sign_up_btn' => $sign_up_btn,
            'lost_password_btn' => $lost_password_btn,
            'wp_login_form' => [
                'redirect' => $redirect,
                'form_id' => $form_id,
                'label_username' => $label_username,
                'label_password' => $label_password,
                'label_remember' => $label_remember,
                'label_log_in' => $label_log_in,
                'remember' => $remember
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


