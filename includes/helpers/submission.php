<?php

/**
 * @param $growtype_quiz_unique_hash
 * @return mixed|null
 */
function growtype_form_get_latest_submission_by_growtype_quiz_unique_hash($growtype_quiz_unique_hash)
{
    global $wpdb;

    if (empty($growtype_quiz_unique_hash)) {
        return null;
    }

    $table_name = $wpdb->prefix . 'postmeta';

    $submission = $wpdb->get_row('SELECT * FROM ' . $table_name . ' WHERE meta_key="submitted_values" and meta_value like "%' . $growtype_quiz_unique_hash . '%" ORDER BY meta_id desc', ARRAY_A);
    $submission = isset($submission['meta_value']) ? $submission['meta_value'] : null;
    $submission = !empty($submission) ? json_decode($submission, true) : null;

    return $submission;
}


function growtype_form_check_if_value_exists_among_submitted_values($value_details, $submission_id = null)
{
    global $wpdb;

    if (empty($value_details)) {
        return null;
    }

    $table_name = $wpdb->prefix . 'postmeta';

    $value_to_search = '"' . $value_details['key'] . '":"' . $value_details['value'] . '"';

    $sql = $wpdb->prepare(
        "SELECT * 
    FROM {$wpdb->prefix}postmeta 
    WHERE meta_key = 'submitted_values' 
    AND meta_value LIKE %s
    ORDER BY meta_id DESC",
        '%' . $wpdb->esc_like($value_to_search) . '%'
    );

    if (!empty($submission_id)) {
        $sql = $wpdb->prepare(
            "SELECT * 
        FROM {$wpdb->prefix}postmeta 
        WHERE meta_key = 'submitted_values' 
        AND meta_value LIKE %s
        AND post_id = %d
        ORDER BY meta_id DESC",
            '%' . $wpdb->esc_like($value_to_search) . '%',
            $submission_id
        );
    }

    $submission = $wpdb->get_row($sql, ARRAY_A);
    $submission = isset($submission['meta_value']) ? $submission['meta_value'] : null;
    $submission = !empty($submission) ? json_decode($submission, true) : null;

    return $submission;
}

/**
 * @param $form_data
 * @param $submitted_values
 * @return array
 */
function growtype_form_save_submission($form_data, $submitted_values)
{
    /**
     * Filter submitted values
     */
    $submitted_values = apply_filters('growtype_form_post_submitted_values', $submitted_values);
    $submitted_data = isset($submitted_values['data']) ? $submitted_values['data'] : [];

    if (get_option('growtype_form_settings_prevent_duplicate_submissions')) {
        global $wpdb;

        $meta_key = 'submitted_values';
        $meta_value = json_encode($submitted_values);

        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            $meta_key,
            $meta_value
        );

        $result = $wpdb->get_results($query);

        if (!empty($result)) {
            return [
                'success' => false,
                'messages' => __("You have already submitted this form.", "growtype-form")
            ];
        }
    }

    $post_type = isset($form_data['post_type']) && !empty($form_data['post_type']) ? $form_data['post_type'] : Growtype_Form_Admin_Submission::POST_TYPE_NAME;

    /**
     * Set post title
     */
    $post_title = date('Y-m-d H:i:s');

    $growtype_form_settings_post_saving_post_title_name = isset($form_data['post_title_name_key']) ? $form_data['post_title_name_key'] : get_option('growtype_form_settings_post_saving_post_title_name');

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

    $post_author = isset($submitted_data[Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID]) ? $submitted_data[Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID] : null;
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

    $values_excluded_from_submission_content = [
        Growtype_Form_Crud::GROWTYPE_FORM_NAME_IDENTIFICATOR,
        Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR,
        Growtype_Form_Crud::GROWTYPE_FORM_LANGUAGE,
    ];

    foreach ($values_excluded_from_submission_content as $value) {
        if (isset($submission_content_values[$value])) {
            unset($submission_content_values[$value]);
        }
    }

    $formatted_content = '';

    /**
     * Format post content
     */
    if (!empty($submission_content_values)) {
        ob_start();

        echo growtype_form_include_view('post.content', [
            'submitted_data' => $submission_content_values,
        ]);

        $formatted_content = ob_get_clean();
    }

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

    /**
     * Filter post args
     */
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
        if ($post_type === Growtype_Form_Admin_Submission::POST_TYPE_NAME) {
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

        /**
         * Insert lead
         */
        $email = Growtype_Form_Admin_Submission::get_email($post_id);

        if (!empty($email)) {
            Growtype_Form_Admin_Lead::insert([
                'title' => $email
            ]);
        }
    }

    $response['post_id'] = isset($post_id) ? $post_id : null;
    $response['post_content'] = $post_content;
    $response['success'] = true;
    $response['messages'] = apply_filters('growtype_form_upload_post_success_message', (isset($form_data['success_message']) ? $form_data['success_message'] : __("Form has been submitted successfully.", "growtype-form")), $submitted_data, $form_data);

    return $response;
}
