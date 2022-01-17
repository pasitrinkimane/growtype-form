<?php

/**
 * Class Growtype_Form_Wc_Crud
 * Woocommerce crud
 */
class Growtype_Form_Crud
{
    use User;

    const EXCLUDED_VALUES_FROM_VALIDATION = [
        Growtype_Form_Render::GROWTYPE_FORM_SUBMITTED_INPUT,
        Growtype_Form_Render::GROWTYPE_FORM_SUBMITTER_ID,
        Growtype_Form_Render::GROWTYPE_FORM_NAME_IDENTIFICATOR,
        Growtype_Form_Render::GROWTYPE_FORM_POST_IDENTIFICATOR
    ];

    const EXCLUDED_VALUES_FROM_SAVING = ['username', 'password', 'repeat_password', 'email', 'submit', 'growtype_form_submitted'];

    public function __construct()
    {
        add_filter('wp_loaded', array ($this, 'growtype_form_process_posted_data'));
    }

    /**
     * @return void
     */
    function growtype_form_process_posted_data()
    {
        /**
         * Process posted values
         */
        if (isset($_POST[Growtype_Form_Render::GROWTYPE_FORM_SUBMITTED_INPUT]) && sanitize_text_field($_POST[Growtype_Form_Render::GROWTYPE_FORM_SUBMITTED_INPUT]) === 'true') {

            $form_name = sanitize_text_field($_POST[Growtype_Form_Render::GROWTYPE_FORM_NAME_IDENTIFICATOR]);

            $submitted_values = [
                'files' => $_FILES,
                'data' => $_POST,
            ];

            $response = $this->process_form_submitted_values($form_name, $submitted_values);

            wp_redirect($response);
            exit();
        }
    }

    /**
     * @param $form_name
     * @param $submitted_data
     * @return false|string|WP_Error
     */
    function process_form_submitted_values($form_name, $submitted_values)
    {
        /**
         * Get form data
         */
        $form_data = $this->get_growtype_form_data($form_name);

        if (empty($form_data)) {
            return null;
        }

        /**
         * Validate if submitted values match available values
         */
        $submitted_values_sanitized = $this->sanitize_form_submitted_values($form_data, $submitted_values);
        $submitted_data = $submitted_values['data'];

        if (!empty($submitted_values_sanitized)) {

            $success_message = $form_data['success_message'] ?? null;

            if ($form_name === 'signup') {
                $submit_data = $this->save_submitted_signup_data($submitted_data);

                if ($submit_data['success']) {
                    $user_id = $submit_data['user_id'];
                    $user = get_user_by('id', $user_id);

                    if ($user) {
                        wp_set_current_user($user_id, $user->user_login);
                        wp_set_auth_cookie($user_id);
                        do_action('wp_login', $user->user_login, $user);

                        if (!growtype_form_redirect_url_after_signup()) {
                            error_log('Redirect url is missing. growtype-form');
                            return __("Something went wrong. Please contact administrator.", "growtype-form");
                        }

                        return growtype_form_redirect_url_after_signup();
                    }
                }
            } elseif (str_contains($form_name, 'wc_product')) {

                require_once Growtype_Form_Path . 'includes/methods/crud/class-growtype-form-wc-crud.php';

                $product_data = $submitted_values;

                /**
                 * Format long description if contains multiple descriptions
                 */
                if (isset($product_data['data']['description']) && is_array($product_data['data']['description'])) {

                    $description_fields = [];
                    foreach ($form_data['main_fields'] as $field) {
                        if (str_contains($field['name'], 'description')) {
                            $field_key = str_replace('description[', '', $field['name']);
                            $field_key = str_replace(']', '', $field_key);
                            $description_fields[$field_key] = $field;
                        }
                    }

                    $description_formatted = '';
                    foreach ($product_data['data']['description'] as $key => $description) {
                        $label = $description_formatted[$key]['label'];
                        $description_formatted .= '<b>' . $label . '</b>' . "\n" . $description . "\n" . "\n";
                    }

                    $product_data['data']['description'] = $description_formatted;
                }

                $wc_crud = new Growtype_Form_Wc_Crud();

                /**
                 * Check if product is set
                 */
                $product = null;

                if (isset($product_data['data'][Growtype_Form_Render::GROWTYPE_FORM_POST_IDENTIFICATOR])) {
                    $product = wc_get_product($product_data['data'][Growtype_Form_Render::GROWTYPE_FORM_POST_IDENTIFICATOR]);

                    if ($product && !$this->user_has_uploaded_product($product->get_id())) {
                        $product = null;
                    }
                }

                if (!empty($product)) {
                    $submit_data = $wc_crud->create_or_update_product($product_data, $product);
                } else {
                    $submit_data = $wc_crud->create_or_update_product($product_data);
                }

                /**
                 * Status
                 */
                if ($submit_data['success']) {
                    $product_id = $submit_data['product_id'];

                    $submit_data['product_id'] = $product_id;
                    $submit_data['success'] = true;
                    $submit_data['message'] = $success_message ?? __('Product uploaded.', 'growtype-form');

                    $redirect_url = growtype_form_redirect_url_after_product_creation();

                    if (!empty($redirect_url)) {
                        return $redirect_url;
                    }
                }
            } elseif ($form_name === 'post') {

                require_once Growtype_Form_Path . 'includes/methods/crud/class-growtype-form-wp-crud.php';

                $wc_crud = new Growtype_Form_Wp_Crud();

                $submit_data = $wc_crud->upload_post($form_data, $submitted_values);

                /**
                 * Attach featured image
                 */
                if (isset($submitted_values['files']) && isset($submitted_values['files']['featured_image'])) {
                    $featured_image = $wc_crud->post_attach_featured_image($submit_data['post_id'], $submitted_values['files']['featured_image']);
                }

                /**
                 * Success
                 */
                if ($submit_data['success']) {
                    $post_id = $submit_data['post_id'];

                    $submit_data['post_id'] = $post_id;
                    $submit_data['success'] = true;
                    $submit_data['message'] = $success_message ?? __('Record created successfully.', 'growtype-form');
                }
            } else {
                $submit_data['success'] = false;
                $submit_data['message'] = __('Wrong data submitted. Please contact site admin.', 'growtype-form');
            }
        } else {
            $submit_data['success'] = false;
            $submit_data['message'] = __('Please fill all required fields.', 'growtype-form');
        }

        /**
         * Prepare redirect details
         */
        $status_args = array (
            'status' => $submit_data['success'] ? 'success' : 'fail',
            'message' => $submit_data['message'],
        );

        $query_args = array_merge($status_args, $submitted_values_sanitized ?? []);

        return add_query_arg($query_args, get_permalink());
    }

    /**
     * @param $form_name
     * @return mixed|null
     */
    public static function get_growtype_form_data($form_name)
    {
        /**
         * Form Settings
         */
        if (str_contains($form_name, 'wc_product')) {
            $form_json_content = get_option('growtype_form_wc_product_json_content');
        } elseif (str_contains($form_name, 'post')) {
            $form_json_content = get_option('growtype_form_post_json_content');
        } elseif (str_contains($form_name, 'signup')) {
            $form_json_content = get_option('growtype_form_signup_json_content');
        } elseif (str_contains($form_name, 'login')) {
            $form_json_content = get_option('growtype_form_login_json_content');
        }

        if (!isset($form_json_content) || empty($form_json_content)) {
            return null;
        }

        $available_forms = json_decode($form_json_content, true);

        if (str_contains($form_name, 'edit') && !isset($available_forms[$form_name]['main_fields'])) {

            $form_parent = str_replace('_edit', '', $form_name);

            if (isset($available_forms[$form_parent]['main_fields'])) {
                $available_forms[$form_name]['main_fields'] = $available_forms[$form_parent]['main_fields'];
            }
        }

        return $available_forms[$form_name] ?? null;
    }

    /**
     * @param $data
     * @return array
     */
    function save_submitted_signup_data($data)
    {
        $email = isset($data['email']) ? sanitize_text_field($data['email']) : null;
        $username = isset($data['username']) ? sanitize_text_field($data['username']) : null;
        $username = !empty($username) ? $username : $email;
        $password = isset($data['password']) ? sanitize_text_field($_REQUEST['password']) : null;
        $repeat_password = isset($data['repeat_password']) ? sanitize_text_field($_REQUEST['repeat_password']) : null;


        if (empty($username) || empty($password) || empty($email)) {
            $response['success'] = false;
            $response['message'] = __("Missing required values", "growtype-form");
            return $response;
        }

        if (!empty($repeat_password)) {
            if ($password !== $repeat_password) {
                $response['success'] = false;
                $response['message'] = __("Passwords do not match", "growtype-form");
                return $response;
            }
        }

        $validate_password = $this->validate_password($password);

        if ($validate_password['success'] === false) {
            $response['success'] = $validate_password['success'];
            $response['message'] = $validate_password['message'];
            return $response;
        }

        /**
         * Save with unique email. Check if username is provided and email already exists in database.
         */
        if ($username !== $email && email_exists($email)) {
            $email_exploded = explode('@', $email);
            $username_formatted = urlencode(str_replace(' ', '', $username));
            $email = $email_exploded[0] . '+' . $username_formatted . '@' . $email_exploded[1];
        }

        $user_id = wp_create_user($username, $password, $email);

        /**
         * Return response
         */
        if (is_wp_error($user_id)) {
            $response['success'] = false;
            $response['message'] = __("Profile already registered.", "growtype-form");
        } else {
            $response = $this->update_user_data($user_id, $data);

            if ($response['success']) {
                $response['message'] = __("Sign up successful.", "growtype-form");
            } else {
                $response['message'] = __("Something went wrong.", "growtype-form");
            }
        }

        return $response;
    }

    /**
     * @param $fields
     * @param $posted_data
     * @return array
     * Map post fields with shortcode fields
     */
    function sanitize_form_submitted_values($form_data, $submitted_values)
    {
        $available_fields = [];

        foreach ($form_data as $key => $field_group) {
            if (str_contains('main_fields', $key) || str_contains('repeater_fields', $key) || str_contains('confirmation_fields', $key)) {
                if (str_contains('repeater_fields', $key)) {
                    array_push($available_fields, array_column($field_group[0]['fields'], 'name'));
                } else {
                    array_push($available_fields, array_column($field_group, 'name'));
                }
            }
        }

        /**
         * Flatten array
         */
        array_walk_recursive($available_fields, function ($v) use (&$required_fields_names) {
            $required_fields_names[] = $v;
        });

        /**
         * Submitted values
         */
        $submitted_data = array_merge($submitted_values['data'], $submitted_values['files']);

        $submitted_values_sanitized = [];
        $submitted_values_notsanitized = [];
        foreach ($submitted_data as $key => $value) {
            if (in_array($key, self::EXCLUDED_VALUES_FROM_VALIDATION)) {
                continue;
            }

            if (!in_array($key, $required_fields_names)) {
                array_push($submitted_values_notsanitized, $key);
                continue;
            }

            /**
             * Prepare keys for return
             */
            if ($key === 'name') {
                $key = Growtype_Form_Render::ALTERNATIVE_SUBMITTED_DATA_KEYS[$key];
            }

            $submitted_values_sanitized[$key] = $value;
        }

        /**
         * Recheck values which did not passed initial validation
         */
        if (!empty($submitted_values_notsanitized)) {
            $passed_values = [];
            foreach ($submitted_values_notsanitized as $value) {
                $match = array_filter($required_fields_names, function ($key) use ($value) {
                    return str_contains($value, $key);
                });

                if ($match) {
                    array_push($passed_values, $value);
                }
            }
            if ($passed_values !== $submitted_values_notsanitized) {
                return null;
            }
        }

        return $submitted_values_sanitized;
    }

    /**
     * @param $data
     * @return array
     */
    function update_user_data($user_id, $data)
    {
        /**
         * Skip values
         */
        $skipped_values = self::EXCLUDED_VALUES_FROM_SAVING;

        /**
         * Save extra values
         */
        foreach ($data as $key => $value) {
            if (!in_array($key, $skipped_values) && !str_contains($value, 'password') && !empty($value)) {
                if ($key === 'first_and_last_name') {
                    $first_name = explode(' ', $value)[0] ?? null;
                    $last_name = explode(' ', $value)[1] ?? null;
                    $middle_name = explode(' ', $value)[2] ?? null;
                    if (empty($middle_name)) {
                        update_user_meta($user_id, 'first_name', sanitize_text_field($first_name));
                        update_user_meta($user_id, 'last_name', sanitize_text_field($last_name));
                    } else {
                        update_user_meta($user_id, 'first_name', sanitize_text_field($value));
                    }
                }
                update_user_meta($user_id, $key, sanitize_text_field($value));
            }
        }

        /**
         * Hide admin bar
         */
        update_user_meta($user_id, 'show_admin_bar_front', 'false');

        /**
         * Get user
         */
        $user = new WP_User($user_id);

        /**
         * Set default user role
         */
        if (!empty(get_option('growtype_form_default_user_role'))) {
            $user->set_role(get_option('growtype_form_default_user_role'));
        }

        /**
         * Return response
         */
        $response['user_id'] = $user_id;
        $response['success'] = true;

        return $response;
    }

    /**
     * @param $password
     * @return array
     * Validate password
     */
    function validate_password($password)
    {
        $status['success'] = true;

        if (!empty($password)) {

            $allow_simple_password = get_option('growtype_form_allow_simple_password');

            if ($allow_simple_password) {
                return $status;
            }

            if (strlen($password) <= '8') {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 8 Characters!", "growtype-registration");
            } elseif (!preg_match("#[0-9]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Number!", "growtype-registration");
            } elseif (!preg_match("#[A-Z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Capital Letter!", "growtype-registration");
            } elseif (!preg_match("#[a-z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Lowercase Letter!", "growtype-registration");
            }
        } else {
            $status['success'] = false;
            $status['message'] = __("Please enter password.", "growtype-registration");
        }

        return $status;
    }
}
