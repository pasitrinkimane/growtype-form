<?php

/**
 * Handles CRUD operations for gf_lead.
 */
class Growtype_Form_Admin_Lead_Crud
{
    public static function insert($data)
    {
        if (isset($data["title"]) && !empty($data["title"])) {
            $existing_posts = self::get_all_by_title($data["title"]);

            if (empty($existing_posts)) {
                $post_id = wp_insert_post([
                    "post_type" => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
                    "post_title" => $data["title"],
                    "post_status" => $data["status"] ?? "publish",
                ]);

                // Save extra details as post meta
                $extra_details = $data["extra_details"] ?? [];
                foreach ($extra_details as $key => $value) {
                    if ($value === "" || $value === null) {
                        continue;
                    }
                    update_post_meta($post_id, $key, $value);
                }

                return $post_id;
            }
        }

        return 0;
    }

    public static function get_all_by_title($title)
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->posts WHERE post_type='%s' AND post_title= '%s'",
                Growtype_Form_Admin_Lead::POST_TYPE_NAME,
                $title,
            ),
        );
    }

    public static function get_all_by_titles($titles)
    {
        global $wpdb;

        $titles = array_values(array_unique(array_filter($titles, static function ($title) {
            return is_string($title) && $title !== '';
        })));

        if (empty($titles)) {
            return [];
        }

        $title_placeholders = implode(', ', array_fill(0, count($titles), '%s'));
        $query_args = array_merge([Growtype_Form_Admin_Lead::POST_TYPE_NAME], $titles);

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = %s AND post_title IN ($title_placeholders)",
                $query_args,
            ),
        );
    }

    public static function get_by_title($title)
    {
        $all = self::get_all_by_title($title);
        if (count($all) > 1) {
            error_log(
                "Growtype Form - !!!IMPORTANT!!! Multiple leads found with the same title: " .
                    $title,
            );
            return null;
        }

        return $all[0] ?? null;
    }

    public static function is_edit_post_type()
    {
        global $post;

        return isset($_GET["action"]) &&
            $_GET["action"] === "edit" &&
            !empty($post) &&
            $post->post_type === Growtype_Form_Admin_Lead::POST_TYPE_NAME;
    }
}
