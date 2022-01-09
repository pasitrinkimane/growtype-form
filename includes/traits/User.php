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

trait User
{
    /**
     * @param $product_id
     * @param $user_id
     * @return bool
     */
    public function user_has_uploaded_product($product_id, $user_id = null)
    {
        $user_id = $user_id ?? wp_get_current_user()->ID ?? null;

        if (empty($user_id)) {
            return false;
        }

        $creator_id = (int)get_post_meta($product_id, '_product_creator_id', true);

        return $creator_id === $user_id;
    }
}
