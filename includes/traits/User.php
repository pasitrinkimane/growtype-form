<?php

/**
 * Provide user methods
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/partials
 */
trait User
{
    /**
     * @return WP_User|null
     */
    public function get_user_data($user_id = null)
    {
        $user_data['profile_data'] = wp_get_current_user()->data;
        $user_data['signup_data'] = Growtype_Form_Signup::get_signup_data();

        return $user_data;
    }
}
