<?php

/**
 *
 */
class Growtype_Form_Signup
{
    const URL_PATH = 'signup';

    public function __construct()
    {
        add_action('init', array ($this, 'custom_url'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));
        add_filter('document_title_parts', array ($this, 'custom_document_title_parts'));
    }

    /**
     * @param $title
     * @return string
     */
    function custom_document_title_parts($title_parts)
    {
        if (growtype_form_signup_page_is_active() && growtype_form_signup_page_ID() === 'default') {
            $title_parts['title'] = __('Sign up', 'growtype-form');
        }

        return $title_parts;
    }

    /**
     * @return void
     */
    function custom_url()
    {
        if (growtype_form_signup_page_ID() === 'default') {
            add_rewrite_endpoint(self::URL_PATH, EP_ROOT);
        }
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            if (growtype_form_signup_page_is_active() && growtype_form_signup_page_ID() === 'default') {
                echo growtype_form_include_view('signup.default');
                exit;
            }
        }
    }

    /**
     * @param $user
     * @return array
     */
    public static function get_signup_data($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }

        $user_meta = get_user_meta($user_id);

        $form_name = isset($user_meta['growtype_form_name']) ? $user_meta['growtype_form_name'][0] : null;
        $json_form_encoded = get_option('growtype_form_signup_json_content');
        $json_form = json_decode($json_form_encoded, true);
        $form_data = isset($json_form[$form_name]) ? $json_form[$form_name] : $json_form['signup'];
        $main_fields = isset($form_data['main_fields']) ? $form_data['main_fields'] : [];
        $confirmation_fields = isset($form_data['confirmation_fields']) ? $form_data['confirmation_fields'] : [];

        /**
         * Main fields
         */
        $user_data = [];
        foreach ($main_fields as $field) {
            $field_name = $field['name'] ?? null;
            $field_type = $field['type'] ?? null;

            if ($field['type'] === 'custom') {
                continue;
            }

            if ($field_type === 'repeater') {
                foreach ($user_meta as $meta_key => $meta_value) {
                    if (str_contains($meta_key, $field_name)) {
                        $json_data = unserialize($meta_value[0]);
                        $json_data_formatted = '';
                        foreach ($json_data as $key => $value) {
                            $json_data_formatted .= $key . ' - ' . $value . ",\n";
                        }
                        $user_data[$meta_key] = [
                            'label' => $meta_key,
                            'value' => $json_data_formatted
                        ];
                    }
                }
            } else {
                $meta_value = isset($user_meta[$field_name]) ? $user_meta[$field_name][0] : null;
                if (!empty($meta_value)) {
                    $user_data[$field['name']] = [
                        'label' => $field['label'] ?? $field['name'] ?? null,
                        'value' => $meta_value
                    ];
                }
            }
        }

        foreach ($confirmation_fields as $field) {
            if (isset($user_meta[$field['name']])) {
                $user_data[$field['name']] = [
                    'label' => $field['label'],
                    'value' => isset($user_meta[$field['name']][0]) ? $user_meta[$field['name']][0] : 'false'
                ];
            }
        }

        return $user_data;
    }

    /**
     * @param $username
     * @param $password
     * @param $email
     * @return false|int|WP_Error
     */
    public static function create_user($username, $password, $email)
    {
        if (!validate_username($username)) {
            return [
                'success' => false,
                'message' => __("Not a valid username. </br></br> Please check the following criteria for a valid username: </br>
- Your username must contain at least 3 characters. </br>
- It may only consist of letters (a-z, A-Z), numbers (0-9), hyphens (-), and underscores (_). </br>
- Special characters and spaces are not allowed. </br>
- Ensure there are no consecutive hyphens or underscores.</br>
- The username should not start or end with a hyphen or underscore. </br>
</br>
Please review your username and make the necessary corrections to meet these requirements.", 'growtype-form'),
                'user_id' => null,
            ];
        }

        if (class_exists('woocommerce')) {
            $user_id = wc_create_new_customer(sanitize_email($email), wc_clean($username), $password);
        } else {
            $user_id = wp_create_user($username, $password, sanitize_email($email));
        }

        if (is_wp_error($user_id)) {
            $message = __('Something went wrong, please try again.', 'growtype-form');

            if (isset($user_id->errors['existing_user_login'])) {
                $message = $user_id->errors['existing_user_login'][0];
            }

            return [
                'success' => false,
                'message' => $message,
                'user_id' => null,
            ];
        }

        /**
         * Update display name
         */
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);

        if (!empty($first_name) && !empty($last_name)) {
            $display_name = $first_name . ' ' . $last_name;
        } else {
            $display_name = strstr($username, '@', true);
        }

        $userdata = array (
            'ID' => $user_id,
            'display_name' => $display_name,
        );

        wp_update_user($userdata);

        /**
         * External modifications
         */
        do_action('growtype_form_create_user', $user_id);

        return [
            'success' => true,
            'message' => 'success',
            'user_id' => $user_id,
        ];
    }
}
