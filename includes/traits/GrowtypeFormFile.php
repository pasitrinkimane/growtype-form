<?php

/**
 *
 */
trait GrowtypeFormFile
{
    /**
     * @param $files
     * @return void
     * Multiple files upload
     */
    public static function upload_files_to_media_library($files)
    {
        if (count($files) === count($files, COUNT_RECURSIVE)) {
            throw new Exception('Use multidimensional file input name in settings. F.e. files[].');
        }

        $uploaded_files = [];
        $formatted_file = [];
        $files_amount = count($files['name']);
        for ($x = 0; $x < $files_amount; $x++) {
            $formatted_file['name'] = $files['name'][$x];
            $formatted_file['type'] = $files['type'][$x];
            $formatted_file['tmp_name'] = $files['tmp_name'][$x];
            $formatted_file['error'] = $files['error'][$x];
            $formatted_file['size'] = $files['size'][$x];

            $upload = self::upload_file_to_media_library($formatted_file);

            if (!empty($upload)) {
                array_push($uploaded_files, $upload);
            }
        }

        return $uploaded_files;
    }

    /**
     * @param $file
     * @return array
     */
    public static function upload_file_to_media_library($file)
    {
        if (!empty($file['error'])) {
            return '';
        }

        $file_name = basename($file["name"]);
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_mime = isset($file['tmp_name']) ? mime_content_type($file['tmp_name']) : '';

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_featured_image = wp_handle_upload($file, array ('test_form' => false));

        if (isset($upload_featured_image['error'])) {
            $response['success'] = false;
            $response['messages'] = $upload_featured_image['error'];

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
