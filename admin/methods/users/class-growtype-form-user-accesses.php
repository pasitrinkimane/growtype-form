<?php
/**
 * Members Admin
 *
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Load Members admin area.
 *
 * @since 2.0.0
 */
class Growtype_Form_User_Accesses
{
    /**
     * Constructor method.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_access'), 100);
    }

    function admin_access()
    {
        /**
         * Prevent admin access for basic roles
         */
        if (
            current_user_can('lead')
            || current_user_can('subscriber')
        ) {
            if (is_user_logged_in()) {
                wp_redirect(growtype_form_profile_page_url());
            } else {
                wp_redirect(growtype_form_login_page_url());
            }
        }
    }
}

