<?php

/**
 *
 */
trait File
{
    /**
     * @param $file
     * @return array
     */
    public function upload_file_to_media_library($file)
    {
        $file_name = basename($file["name"]);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_mime = mime_content_type($file['tmp_name']);

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_featured_image = wp_handle_upload($file, array ('test_form' => false));

        if (isset($upload_featured_image['error'])) {
            $response['success'] = false;
            $response['message'] = $upload_featured_image['error'];

            return $response;
        }

        $upload_featured_image_path = $upload_featured_image['file'];

        $upload_id = wp_insert_attachment(array (
            'guid' => $upload_featured_image_path,
            'post_mime_type' => $file_mime,
            'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
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
