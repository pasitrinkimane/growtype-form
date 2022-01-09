<?php

/**
 * Class Growtype_Form_Wp_Crud
 * Wordpress crud
 */
class Growtype_Form_Wp_Crud
{
    /**
     * @param $form_data
     * @param $submitted_values
     * @return array
     */
    function upload_post($form_data, $submitted_values)
    {
        $post_type = $form_data['post_type'] ?? null;
        $post_title = $submitted_values['data']['title'] ?? null;
        $post_author = $submitted_values['data']['form_submitter_id'] ?? null;
        $post_status = $submitted_values['data']['post_status'] ?? 'draft';
        $submitted_data = $submitted_values['data'];
        $post_tags = $submitted_values['data']['tags'] ?? null;

        /**
         * Unset unnecessary values from submitted data
         */
        unset($submitted_data['form_submitter_id']);
        unset($submitted_data['growtype_form_submitted']);
        unset($submitted_data['terms_and_conditions']);

        /**
         * Format post content
         */
        $post_content = $submitted_values['data']['post_content'] ?? implode(", ", $submitted_data);

        /**
         * Create array
         */
        $post_arr = array (
            'post_type' => $post_type,
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => $post_status,
            'post_author' => $post_author
        );

        /**
         * Save post
         */
        $post = wp_insert_post($post_arr);

        if (is_wp_error($post)) {
            $response['success'] = false;
            $response['message'] = __("Something went wrong. Please contact administrator.", "growtype-form");

            return $response;
        }

        /**
         * Add tags
         */
        if (!empty($post_tags)) {
            wp_add_post_tags($post, $post_tags);
        }

        $response['post_id'] = $post;
        $response['success'] = true;

        return $response;
    }

    /**
     * Attach featured image
     */
    function post_attach_featured_image($post_id, $featured_image)
    {
        if (!empty($post_id) && !empty($featured_image)) {
            $featured_image = self::upload_file_to_media_library($featured_image);

            if (isset($featured_image['attachment_id'])) {
                return set_post_thumbnail($post_id, $featured_image['attachment_id']);
            }
        }

        return null;
    }

    /**
     * @return array|int|WP_Error
     */
    function upload_file_to_media_library($featured_image)
    {
        $featured_image_name = basename($featured_image["name"]);
        $featured_image_extension = pathinfo($featured_image_name, PATHINFO_EXTENSION);
        $featured_image_mime = mime_content_type($featured_image['tmp_name']);

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_featured_image = wp_handle_upload($featured_image, array ('test_form' => false));

        if (isset($upload_featured_image['error'])) {
            $response['success'] = false;
            $response['message'] = $upload_featured_image['error'];

            return $response;
        }

        $upload_featured_image_path = $upload_featured_image['file'];

        $upload_id = wp_insert_attachment(array (
            'guid' => $upload_featured_image_path,
            'post_mime_type' => $featured_image_mime,
            'post_title' => preg_replace('/\.[^.]+$/', '', $featured_image_name),
            'post_content' => '',
            'post_status' => 'inherit'
        ), $upload_featured_image_path);

        // wp_generate_attachment_metadata() won't work if you do not include this file
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Generate and save the attachment metas into the database
        wp_update_attachment_metadata($upload_id, wp_generate_attachment_metadata($upload_id, $upload_featured_image_path));

        $response['attachment_id'] = $upload_id;

        return $response;
    }
}
