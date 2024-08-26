<?php

/**
 *
 */
trait GrowtypeFormPost
{
    /**
     * Attach featured image
     */
    public static function post_attach_featured_image($post_id, $featured_image)
    {
        if (!empty($post_id) && !empty($featured_image)) {

            if (is_array($featured_image['name'])) {
                $featured_images = [];
                $fileCount = count($featured_image['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $name = $featured_image['name'][$i];
                    $type = $featured_image['type'][$i];
                    $tmp_name = $featured_image['tmp_name'][$i];
                    $error = $featured_image['error'][$i];
                    $size = $featured_image['size'][$i];

                    $featured_images[] = [
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => $tmp_name,
                        'error' => $error,
                        'size' => $size,
                    ];
                }

                foreach ($featured_images as $featured_image) {
                    $featured_image = self::upload_file_to_media_library($featured_image);
                }
            } else {
                $featured_image = self::upload_file_to_media_library($featured_image);
            }

            if (isset($featured_image['attachment_id'])) {
                return set_post_thumbnail($post_id, $featured_image['attachment_id']);
            }
        }

        return null;
    }

    /**
     * Attach featured image
     */
    public static function post_attach_files($post_id, $files)
    {
        if (!empty($post_id) && !empty($files)) {
            $file_urls = [];
            $uploaded_attachments = [];
            foreach ($files as $file) {
                $uploaded_files = self::upload_files_to_media_library($file);

                if (!empty($uploaded_files)) {
                    foreach ($uploaded_files as $uploaded_file) {

                        wp_update_post(array (
                            'ID' => $uploaded_file['attachment_id'],
                            'post_parent' => $post_id,
                        ), true);

                        $file_url = wp_get_attachment_url($uploaded_file['attachment_id']);

                        array_push($file_urls, $file_url);
                        array_push($uploaded_attachments, $uploaded_file);
                    }
                }
            }

            if (!empty($file_urls)) {
                $post = get_post($post_id);
                $post_content = $post->post_content;
                $post_content .= '<br><br><h3><b>Files:</b></h3><br>' . implode('<br>', $file_urls);

                update_post_meta($post_id, 'uploaded_attachments', json_encode($uploaded_attachments));

                return wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $post_content
                ]);
            }
        }

        return null;
    }
}
