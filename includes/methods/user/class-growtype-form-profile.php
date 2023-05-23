<?php

/**
 *
 */
class Growtype_Form_Profile
{
    use User;

    const URL_SLUG = 'profile';

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
//        wp_enqueue_style('growtype-form-profile', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/profile/main.css', array (), '1.1', 'all');
    }

    /**
     * @return void
     */
    function custom_url()
    {
        add_rewrite_endpoint(self::URL_SLUG, EP_ALL);
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $page_slug = parse_url($_SERVER['REQUEST_URI']);
            $page_slug = isset($page_slug['path']) ? str_replace('/', '', $page_slug['path']) : '';

            if (growtype_form_profile_page_is_active() && growtype_form_profile_page_ID() === 'default' && $page_slug === self::URL_SLUG) {

                /**
                 * Add profile page class to body
                 */
                add_filter('body_class', array ($this, 'growtype_form_profile_body_class'));

                if (is_user_logged_in()) {
                    /**
                     * Add user data
                     */
                    $data['user'] = $this->get_user_data();

                    /**
                     * Template
                     */
                    echo growtype_form_include_view('profile.default', ['data' => $data]);
                } else {
                    wp_redirect(growtype_form_login_page_url());
                }

                exit;
            }
        }
    }

    /**
     * Add signup class to body
     */
    function growtype_form_profile_body_class($classes)
    {
        $classes[] = 'profile-page';

        return $classes;
    }
}
