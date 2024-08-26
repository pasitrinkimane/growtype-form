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
        if (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false || strpos($_SERVER['REQUEST_URI'], '/wp-login.php') !== false)) {
            $roles_prevented_from_admin_access = apply_filters('growtype_form_roles_prevented_from_admin_access', [
                'subscriber',
                'lead',
            ]);

            $role_is_prevented = array_filter($roles_prevented_from_admin_access, function ($role) {
                return current_user_can($role);
            });

            if (
                !defined('DOING_AJAX')
                &&
                $role_is_prevented
            ) {
                if (is_user_logged_in()) {
                    wp_redirect(growtype_form_profile_page_url());
                } else {
                    wp_redirect(growtype_form_login_page_url());
                }
            }
        }
    }
}

