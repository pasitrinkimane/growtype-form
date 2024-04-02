<?php

/**
 *
 */
class Growtype_Form_Profile_Settings
{
    use GrowtypeFormUser;

    const URL_PATH = 'profile/settings';

    public function __construct()
    {
        add_action('init', array ($this, 'custom_url'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));
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
        if (growtype_form_profile_settings_page_is_active()) {
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
                echo growtype_form_include_view('profile.settings', ['data' => $data]);
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
        $classes[] = 'profile-settings';

        return $classes;
    }
}
