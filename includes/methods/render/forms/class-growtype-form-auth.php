<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Auth
{
    use GrowtypeFormAuth;
    use GrowtypeFormNotice;

    const GROWTYPE_FORM_SHORTCODE_NAME = 'growtype_form_auth';

    public function __construct()
    {
        if (!is_admin()) {
            add_shortcode(self::GROWTYPE_FORM_SHORTCODE_NAME, array ($this, 'growtype_form_shortcode_function'));
        }
    }

    /**
     * Shortcode
     * [growtype_form_auth]
     */
    function growtype_form_shortcode_function($args)
    {
        $args = !empty($args) ? $args : [];
        $default = $args['default'] ?? 'login';

        if (is_user_logged_in()) {
            return growtype_form_include_view('login.partials.success-content');
        }

        add_action('wp_footer', array ($this, 'growtype_form_show_hide_password_button'), 100);

        ob_start();
        ?>
        <div class="growtype-form-auth">
            <?php
            $growtype_form_general = new Growtype_Form_General();

            echo $this->growtype_form_get_notice();

            $args_login = $args;
            $args_login['name'] = 'login';
            $args_login['class'] = ($default === 'login' ? 'is-active' : '');

            echo $growtype_form_general->form_init($args_login);

            $args_signup = $args;
            $args_signup['name'] = 'signup';
            $args_signup['class'] = ($default === 'signup' ? 'is-active' : '');

            echo $growtype_form_general->form_init($args_signup);
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
