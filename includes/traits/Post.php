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
        $post_type = $form_data['post_type'] ?? null;
        $growtype_form_settings_post_saving_post_title_name = get_option('growtype_form_settings_post_saving_post_title_name', 'title');
        $post_title = isset($submitted_values['data'][$growtype_form_settings_post_saving_post_title_name]) ? $submitted_values['data'][$growtype_form_settings_post_saving_post_title_name] : null;
        $post_author = $submitted_values['data'][Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID] ?? null;
        $post_status = $submitted_values['data']['post_status'] ?? 'draft';
        $submitted_data = $submitted_values['data'];
        $post_tags = $submitted_values['data']['tags'] ?? null;

        /**
         * Unset unnecessary values from submitted data
         */
        unset($submitted_data[Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID]);
        unset($submitted_data[Growtype_Form_Crud::GROWTYPE_FORM_SUBMIT_ACTION]);
        unset($submitted_data['terms_and_conditions']);

        /**
         * Format post content
         */
        ob_start();

        foreach ($submitted_values['data'] as $key => $data) {
            ?>
            <h3><b><?= $key ?></b></h3>
            <p><?= $data ?></p>
            <?php
        }

        $formatted_content = ob_get_clean();

        /**
         * Format post content
         */
        $post_content = $submitted_values['data']['post_content'] ?? $formatted_content;

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
        $response['post_content'] = $post_content;
        $response['message'] = isset($form_data['success_message']) ? $form_data['success_message'] : __("Post has been submitted successfully.", "growtype-form");

        return $response;
    }

    /**
     * Attach featured image
     */
    public function post_attach_featured_image($post_id, $featured_image)
    {
        if (!empty($post_id) && !empty($featured_image)) {
            $featured_image = $this->upload_file_to_media_library($featured_image);

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
                    }
                }
            }

            if (!empty($file_urls)) {
                $post = get_post($post_id);
                $post_content = $post->post_content;
                $post_content .= '<br><br><h3>Files:</h3><br>' . implode('<br>', $file_urls);

                return wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $post_content
                ]);
            }
        }

        return null;
    }
}
