<?php

/**
 *
 */
class Growtype_Form_Profile
{
    use GrowtypeFormUser;

    public function __construct()
    {
        add_action('init', array ($this, 'custom_urls'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));
    }

    public static function user_details()
    {
        $user_id = get_current_user_id();

        if (empty($user_id)) {
            return [];
        }

        $user_meta = get_user_meta($user_id);
        $user = get_userdata($user_id);

        $user_meta['user_email'] = [$user->data->user_email];
        $user_meta['profile_picture'] = [get_user_meta($user_id, 'profile_picture', true)];
        $user_meta['phone_formatted'] = self::get_user_phone();

        return $user_meta;
    }

    public static function get_user_phone($type = 'phone_formatted', $user_id = null)
    {
        $user_id = !empty($user_id) ? $user_id : get_current_user_id();

        $user_meta = get_user_meta($user_id);

        $phone_details = [
            'phone' => $user_meta['phone'][0] ?? '',
            'phone_formatted' => '',
            'country_iso' => $user_meta['phone_country_iso'][0] ?? '',
            'country_code' => $user_meta['phone_country_code'][0] ?? '',
        ];

        if (!empty($phone_details['country_code'])) {
            $country_code = preg_replace('/\D+/', '', $phone_details['country_code']);
            $phone = preg_replace('/\D+/', '', $phone_details['phone']);
            $phone_details['phone_formatted'] = ['+' . $country_code . $phone];
        }

        return $phone_details[$type];
    }

    /**
     * @return void
     */
    function custom_urls()
    {
        $custom_pages = self::custom_pages();

        foreach ($custom_pages as $custom_page) {
            add_rewrite_rule(
                '^' . $custom_page['url'] . '/?$',
                'index.php?' . $custom_page['url'] . '_page=1',
                'top'
            );

            add_rewrite_tag('%' . $custom_page['url'] . '_page%', '([0-1])');
        }
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (is_user_logged_in()) {
            $custom_pages = self::custom_pages();

            global $wp_query;

            foreach ($custom_pages as $custom_page) {
                if (isset($wp_query->query_vars[$custom_page['url'] . '_page']) && $wp_query->query_vars[$custom_page['url'] . '_page'] === '1') {
                    add_filter('body_class', function ($classes) use ($custom_page) {
                        $classes[] = $custom_page['key'] . '-page';
                        return $classes;
                    });

                    $handler_class = $custom_page['handler'] ?? Growtype_Form_Profile::class;
                    $handler = new $handler_class();
                    $data = $handler->page_data();

                    echo growtype_form_include_view($custom_page['view_path'], ['data' => $data]);

                    exit;
                }
            }
        }
    }

    public static function custom_pages()
    {
        $custom_pages = [];

        return apply_filters('growtype_form_user_profile_custom_pages', $custom_pages);
    }

    public function page_data()
    {
        $data['user'] = $this->get_user_data();

        return apply_filters('growtype_form_user_profile_page_data', $data);
    }

    public static function edit_profile_form_fields($form_name, $profile_fields)
    {
        return apply_filters('growtype_form_user_profile_form_fields', $profile_fields, $form_name);
    }

    public static function update_profile_form_details($form_name, $submitted_values, $profile_fields = [])
    {
        $profile_fields = self::edit_profile_form_fields($form_name, $profile_fields);

        foreach ($profile_fields as $field) {
            $field_name = $field['name'] ?? '';
            $field_type = $field['type'] ?? '';
            $user_id = get_current_user_id();

            if (!empty($field_name) && isset($submitted_values[$field_name])) {
                if ($field_name === 'user_email') {
                    $update_result = wp_update_user([
                        'ID' => $user_id,
                        'user_email' => $submitted_values[$field_name],
                    ]);

                    if (is_wp_error($update_result)) {
                        return [
                            'success' => false,
                            'message' => $update_result->get_error_message()
                        ];
                    }
                } elseif ($field_name === 'profile_picture') {
                    $status = self::handle_file_upload($submitted_values[$field_name], 'profile_picture');

                    if (!$status['success']) {
                        return $status;
                    }
                } elseif ($field_type === 'checkbox') {
                    update_user_meta($user_id, sanitize_text_field($field_name), 1);
                } else {
                    update_user_meta($user_id, sanitize_text_field($field_name), $submitted_values[$field_name]);
                }
            } elseif (!empty($field_name)) {
                if ($field_type === 'checkbox') {
                    update_user_meta($user_id, sanitize_text_field($field_name), false);
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Profile updated successfully.'
        ];
    }

    public static function handle_file_upload($uploadedfile, $meta_key)
    {
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $user_id = get_current_user_id();
        $old_photo_url = get_user_meta($user_id, $meta_key, true);
        $uploads = wp_upload_dir();

        $files_to_process = [];
        if (isset($uploadedfile['name']) && is_array($uploadedfile['name'])) {
            foreach ($uploadedfile['name'] as $i => $name) {
                $files_to_process[] = [
                    'name' => $uploadedfile['name'][$i],
                    'type' => $uploadedfile['type'][$i],
                    'tmp_name' => $uploadedfile['tmp_name'][$i],
                    'error' => $uploadedfile['error'][$i],
                    'size' => $uploadedfile['size'][$i],
                ];
            }
        } else {
            $files_to_process = [$uploadedfile];
        }

        foreach ($files_to_process as $single_file) {
            if (empty($single_file['name'])) {
                continue;
            }

            $maximum_file_size = 5;

            // Limit file size to 5MB
            if ($single_file['size'] > $maximum_file_size * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File size exceeds the maximum allowed size of ' . $maximum_file_size . 'MB.'
                ];
            }

            $movefile = wp_handle_upload($single_file, ['test_form' => false]);

            if ($movefile && !isset($movefile['error'])) {
                // Remove old photo
                if ($old_photo_url && strpos($old_photo_url, $uploads['baseurl']) !== false) {
                    $old_photo_path = str_replace($uploads['baseurl'], $uploads['basedir'], $old_photo_url);
                    if (file_exists($old_photo_path)) {
                        wp_delete_file($old_photo_path);
                    }
                }

                update_user_meta($user_id, $meta_key, $movefile['url']);

                break; // only save first uploaded
            } else {
                return [
                    'success' => false,
                    'message' => $movefile['error'] ?? 'Error uploading file.'
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Profile picture updated successfully.'
        ];
    }
}
