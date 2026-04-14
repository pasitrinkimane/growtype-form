<?php

/**
 * Handles custom admin columns for gf_lead.
 */
class Growtype_Form_Admin_Lead_Columns
{
    public function __construct()
    {
        add_filter('manage_' . Growtype_Form_Admin_Lead::POST_TYPE_NAME . '_posts_columns', array ($this, 'set_custom_posts_columns'));
        add_action('manage_' . Growtype_Form_Admin_Lead::POST_TYPE_NAME . '_posts_custom_column', array ($this, 'set_custom_posts_custom_column'), 10, 2);
    }

    /**
     * Admin columns
     */
    function set_custom_posts_columns($columns)
    {
        foreach (Growtype_Form_Admin_Lead_Meta_Boxes::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                $columns[$field['key']] = $field['title'];
            }
        }

        return $columns;
    }

    function set_custom_posts_custom_column($column, $post_id)
    {
        foreach (Growtype_Form_Admin_Lead_Meta_Boxes::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                if ($column === $field['key']) {
                    if (in_array($field['key'], ['user_is_verified', 'auth_method'])) {
                        $user = get_user_by('email', get_the_title($post_id));
                        if (!empty($user)) {
                            if ($field['key'] === 'user_is_verified') {
                                echo get_user_meta($user->ID, 'is_verified', true);
                            } elseif ($field['key'] === 'auth_method') {
                                echo get_user_meta($user->ID, 'auth_method', true);
                            }
                        }
                    } else {
                        echo get_post_meta($post_id, $field['key'], true);
                    }
                }
            }
        }
    }
}
