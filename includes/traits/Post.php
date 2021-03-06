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
        $post_title = $submitted_values['data']['title'] ?? null;
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
}
