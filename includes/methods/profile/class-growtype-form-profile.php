<?php

/**
 *
 */
class Growtype_Form_Profile
{
    const CUSTOM_SLUG = 'profile';

    public function __construct()
    {
        if (!is_admin()) {
            if (growtype_form_profile_page_is_active()) {
                add_action('wp_enqueue_scripts', array ($this, 'growtype_form_enqueue_styles'));
            }
        }

        add_action('init', array ($this, 'custom_url'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));
    }

    /**
     * @return void
     */
    function growtype_form_enqueue_styles()
    {
        wp_enqueue_style('growtype-form-profile', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/profile/main.css', array (), '1.1', 'all');
    }

    /**
     * @return void
     */
    function custom_url()
    {
        add_rewrite_endpoint(self::CUSTOM_SLUG, EP_ALL);
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (!empty($_SERVER['PHP_SELF'])) {
            $page_slug = str_replace('/', '', $_SERVER['PHP_SELF']);

            if (growtype_form_profile_page_is_active() && growtype_form_profile_page_ID() === 'default' && $page_slug === self::CUSTOM_SLUG) {

                if (is_user_logged_in()) {
                    $child_template_path = get_stylesheet_directory() . '/views/growtype-form/';

                    if (file_exists($child_template_path . 'profile/default.blade.php')) {
                        echo growtype_form_include_view('profile/default', [], $child_template_path);
                    } else {
                        echo growtype_form_include_view('profile/default');
                    }
                } else {
                    wp_redirect(growtype_form_signup_page_url());
                }

                exit;
            }
        }
    }
}
