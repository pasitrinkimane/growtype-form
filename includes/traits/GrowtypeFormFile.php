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

//        $uploaded_files = [];
//        $formatted_file = [];
//        $files_amount = count($files['name']);
//        for ($x = 0; $x < $files_amount; $x++) {
//            $formatted_file['name'] = $files['name'][$x];
//            $formatted_file['type'] = $files['type'][$x];
//            $formatted_file['tmp_name'] = $files['tmp_name'][$x];
//            $formatted_file['error'] = $files['error'][$x];
//            $formatted_file['size'] = $files['size'][$x];
//
//            $upload = self::upload_file_to_media_library($formatted_file);
//
//            if (!empty($upload)) {
//                array_push($uploaded_files, $upload);
//            }
//        }

        $grouped_files = self::group_files($files);

        $attachments_ids = [];
        foreach ($grouped_files as $key => $files) {
            foreach ($files as $key => $file) {
                $attachment = self::upload_file_to_media_library($file);
                $attachment_id = $attachment['attachment_id'] ?? '';

                if ($attachment_id) {
                    array_push($attachments_ids, $attachment_id);
                }
            }
        }

        return $attachments_ids;
    }

    public static function group_files($files)
    {
        $grouped_files = [];
        foreach ($files as $key => $files) {
            $files_amount = count($files['name']);

            $grouped_files[$key] = [];
            for ($x = 0; $x < $files_amount; $x++) {
                $formatted_file['name'] = is_array($files['name'][$x]) ? $files['name'][$x][0] : $files['name'][$x];
                $formatted_file['type'] = is_array($files['type'][$x]) ? $files['type'][$x][0] : $files['type'][$x];
                $formatted_file['tmp_name'] = is_array($files['tmp_name'][$x]) ? $files['tmp_name'][$x][0] : $files['tmp_name'][$x];
                $formatted_file['error'] = is_array($files['error'][$x]) ? $files['error'][$x][0] : $files['error'][$x];
                $formatted_file['size'] = is_array($files['size'][$x]) ? $files['size'][$x][0] : $files['size'][$x];

                array_push($grouped_files[$key], $formatted_file);
            }
        }

        return $grouped_files;
    }

    /**
     * @param $file
     * @return array
     */
    public static function upload_file_to_media_library($file, $upload_dir = null)
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

        if (!empty($upload_dir)) {
            add_filter('upload_dir', function ($dirs) use ($upload_dir) {
                $custom_dir = $upload_dir; // Change this to your desired folder name

                $dirs['path'] = $dirs['basedir'] . '/' . $custom_dir;
                $dirs['url'] = $dirs['baseurl'] . '/' . $custom_dir;
                $dirs['subdir'] = '/' . $custom_dir;

                return $dirs;
            });
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

    public static function upload_url_to_media_library($image_url, $upload_dir = null)
    {
        if (empty($image_url)) {
            return [
                'success' => false,
                'message' => 'Image URL is required.'
            ];
        }

        // Get file name from URL
        $file_name = basename(parse_url($image_url, PHP_URL_PATH));
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        // Validate file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp.'
            ];
        }

        // Download image file
        $image_data = file_get_contents($image_url);
        if ($image_data === false) {
            return [
                'success' => false,
                'message' => 'Failed to download image from URL.'
            ];
        }

        // Get upload directory
        $upload_dir_info = wp_upload_dir();
        if (!empty($upload_dir)) {
            $upload_path = $upload_dir_info['basedir'] . '/' . $upload_dir;
            $upload_url = $upload_dir_info['baseurl'] . '/' . $upload_dir;
        } else {
            $upload_path = $upload_dir_info['path'];
            $upload_url = $upload_dir_info['url'];
        }

        // Ensure directory exists
        if (!file_exists($upload_path)) {
            wp_mkdir_p($upload_path);
        }

        // Save image to upload directory
        $file_path = $upload_path . '/' . $file_name;
        file_put_contents($file_path, $image_data);

        // Check MIME type
        $file_mime = mime_content_type($file_path);

        // Create attachment post
        $attachment = [
            'guid'           => $upload_url . '/' . $file_name,
            'post_mime_type' => $file_mime,
            'post_title'     => preg_replace('/\.[^.]+$/', '', $file_name),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        $attach_id = wp_insert_attachment($attachment, $file_path);

        // Include image.php for wp_generate_attachment_metadata()
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Generate and save attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return [
            'success' => true,
            'attachment_id' => $attach_id,
            'url' => wp_get_attachment_url($attach_id)
        ];
    }
}
