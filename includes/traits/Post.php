<?php

/**
 *
 */
trait Post
{
    /**
     * @param $form_data
     * @param $submitted_values
     * @return array
     */
    public function upload_post($form_data, $submitted_values)
    {
        /**
         * Filter submitted values
         */
        $submitted_values = apply_filters('growtype_form_post_submitted_values', $submitted_values);
        $submitted_data = $submitted_values['data'];

        $post_type = isset($form_data['post_type']) ? $form_data['post_type'] : Growtype_Form_Submissions::POST_TYPE_NAME;

        if (empty($post_type)) {
            return null;
        }

        /**
         * Set post title
         */
        $post_title = date('Y-m-d H:i:s');

        $growtype_form_settings_post_saving_post_title_name = get_option('growtype_form_settings_post_saving_post_title_name');

        if (isset($submitted_data[$growtype_form_settings_post_saving_post_title_name]) && !empty($submitted_data[$growtype_form_settings_post_saving_post_title_name])) {
            $post_title = $submitted_data[$growtype_form_settings_post_saving_post_title_name];
        } else {
            $alternative_titles_keys = ['title', 'name'];
            foreach ($alternative_titles_keys as $alternative_title_key) {
                if (isset($submitted_data[$alternative_title_key]) && !empty($submitted_data[$alternative_title_key])) {
                    $post_title = $submitted_data[$alternative_title_key];
                    break;
                }
            }
        }

        /**
         * Filter post title
         */
        $post_title = apply_filters('growtype_form_upload_post_post_title', $post_title, $submitted_values);

        $post_author = $submitted_data[Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID] ?? null;
        $post_status = isset($submitted_data['post_status']) ? $submitted_data['post_status'] : 'draft';
        $post_tags = isset($submitted_data['tags']) ? $submitted_data['tags'] : null;

        /**
         * Unset unnecessary values from submitted data
         */
        foreach (Growtype_Form_Crud::EXCLUDED_VALUES_FROM_SAVING as $value) {
            if (isset($submitted_data[$value])) {
                unset($submitted_data[$value]);
            }
        }

        $submission_content_values = $submitted_data;

        unset($submission_content_values['growtype_form_name']);
        unset($submission_content_values['growtype_form_post_id']);

        /**
         * Format post content
         */
        ob_start();

        echo growtype_form_include_view('post.content', [
            'submitted_data' => $submission_content_values,
        ]);

        $formatted_content = ob_get_clean();

        /**
         * Format post content
         */
        $post_content = isset($submitted_data['post_content']) ? $submitted_data['post_content'] : $formatted_content;

        /**
         * Create array
         */
        $post_args = array (
            'post_type' => $post_type,
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => $post_status,
            'post_author' => $post_author
        );

        $post_args = apply_filters('growtype_form_upload_post_args', $post_args, $submitted_data, $form_data);

        if (!empty($post_args)) {
            /**
             * Save post
             */
            $post_id = wp_insert_post($post_args);

            if (is_wp_error($post_id)) {
                $response['success'] = false;
                $response['messages'] = __("Something went wrong. Please contact administrator.", "growtype-form");

                return $response;
            }

            /**
             * Extra meta values for general submission
             */
            if ($post_type === Growtype_Form_Submissions::POST_TYPE_NAME) {
                update_post_meta($post_id, 'form_name', $form_data['form_name']);
                update_post_meta($post_id, 'submitted_values', json_encode($submitted_values));

                /**
                 * Include IP details
                 */
                $server_http_x_forwarded_for = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
                $server_remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

                update_post_meta($post_id, 'server_http_x_forwarded_for', $server_http_x_forwarded_for);
                update_post_meta($post_id, 'server_remote_addr', $server_remote_addr);
            }

            /**
             * Add tags
             */
            if (!empty($post_tags)) {
                wp_add_post_tags($post_id, $post_tags);
            }
        }

        $response['post_id'] = isset($post_id) ? $post_id : null;
        $response['post_content'] = $post_content;
        $response['success'] = true;
        $response['messages'] = apply_filters('growtype_form_upload_post_success_message', (isset($form_data['success_message']) ? $form_data['success_message'] : __("Form has been submitted successfully.", "growtype-form")), $submitted_data, $form_data);

        return $response;
    }

    /**
     * Attach featured image
     */
    public function post_attach_featured_image($post_id, $featured_image)
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
     * Attach featured image
     */
    public function post_attach_files($post_id, $files)
    {
        if (!empty($post_id) && !empty($files)) {
            $file_urls = [];
            $uploaded_attachments = [];
            foreach ($files as $file) {
                $uploaded_files = $this->upload_files_to_media_library($file);

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
