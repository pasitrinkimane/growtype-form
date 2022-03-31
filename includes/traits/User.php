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
     * @param $user_id
     * @return array
     */
    public function get_user_data($user_id = null)
    {
        $user = !empty($user_id) ? get_user_by('id', $user_id) : wp_get_current_user();
        $user_data['profile_data'] = $user->data;
        $user_data['signup_data'] = Growtype_Form_Signup::get_signup_data($user_id);
        $user_data['child_users'] = $this->get_user_child_users($user->ID);

        return $user_data;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function get_user_child_users($user_id)
    {
        $child_users = [];

        $child_user_ids = get_user_meta($user_id, 'child_user_ids', true);

        if (!empty($child_user_ids)) {
            foreach ($child_user_ids as $user_id) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    $child_users[$user_id]['profile_data'] = $user->data;
                    $child_users[$user_id]['signup_data'] = Growtype_Form_Signup::get_signup_data($user_id);
                }
            }
        }

        return $child_users;
    }
}
