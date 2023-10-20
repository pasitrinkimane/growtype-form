<?php

/**
 *
 */
class Growtype_Form_Profile
{
    use User;

    const URL_PATH = 'profile';

    public function __construct()
    {
        if (!is_admin()) {
            if (growtype_form_profile_page_is_active()) {
                add_action('wp_enqueue_scripts', array ($this, 'growtype_form_enqueue_styles'));
            }
        }

        add_action('init', array ($this, 'custom_url'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));

        add_action('init', array ($this, 'add_custom_roles'));
    }

    function add_custom_roles()
    {
        /**
         * Lead role
         */
        add_role(
            'lead',
            __('Lead'),
            array (
                'read' => false,
                'delete_posts' => false,
                'delete_published_posts' => false,
                'edit_posts' => false,
                'publish_posts' => false,
                'edit_published_posts' => false,
                'upload_files' => false,
                'moderate_comments' => false,
            )
        );
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
        add_rewrite_endpoint(self::URL_PATH, EP_ALL);
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (growtype_form_profile_page_is_active()) {
            /**
             * Add profile page class to body
             */
            add_filter('body_class', array ($this, 'body_class'));

            if (is_user_logged_in()) {
                /**
                 * Add user data
                 */
                $data['user'] = $this->get_user_data();

                /**
                 * Template
                 */
                echo growtype_form_include_view('profile.index', ['data' => $data]);
            } else {
                wp_redirect(growtype_form_login_page_url());
            }

            exit;
        }
    }

    /**
     * Add signup class to body
     */
    function body_class($classes)
    {
        $classes[] = 'profile-page';

        return $classes;
    }
}
