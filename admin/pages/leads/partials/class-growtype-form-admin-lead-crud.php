<?php

/**
 * Handles CRUD operations for gf_lead.
 */
class Growtype_Form_Admin_Lead_Crud
{
    public static function insert($data)
    {
        if (isset($data['title']) && !empty($data['title'])) {
            $existing_posts = self::get_all_by_title($data['title']);

            if (empty($existing_posts)) {
                wp_insert_post([
                    'post_type' => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
                    'post_title' => $data['title'],
                    'post_status' => $data['status'] ?? 'publish'
                ]);
            }
        }
    }

    public static function get_all_by_title($title)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type='%s' AND post_title= '%s'", Growtype_Form_Admin_Lead::POST_TYPE_NAME, $title));
    }

    public static function get_by_title($title)
    {
        $all = self::get_all_by_title($title);
        if (count($all) > 1) {
            error_log('Growtype Form - !!!IMPORTANT!!! Multiple leads found with the same title: ' . $title);
            return null;
        }

        return $all[0] ?? null;
    }

    public static function is_edit_post_type()
    {
        global $post;

        return (isset($_GET['action']) && $_GET['action'] === 'edit') && !empty($post) && $post->post_type === Growtype_Form_Admin_Lead::POST_TYPE_NAME;
    }
}
