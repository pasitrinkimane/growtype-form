<?php

/**
 * Class Growtype_Form_Wc_Crud
 * Woocommerce crud
 */
class Growtype_Form_Crud
{
    use Post;
    use User;
    use File;
    use Product;
    use Notice;

    const EXCLUDED_VALUES_FROM_VALIDATION = [
        self::GROWTYPE_FORM_SUBMIT_ACTION,
        self::GROWTYPE_FORM_SUBMITTER_ID,
        self::GROWTYPE_FORM_NAME_IDENTIFICATOR,
        self::GROWTYPE_FORM_POST_IDENTIFICATOR,
        self::GROWTYPE_FORM_SPAM_IDENTIFICATOR,
        self::GROWTYPE_FORM_REDIRECT_AFTER,
        self::GROWTYPE_QUIZ_UNIQUE_HASH,
        'preloaded',
    ];

    const INCLUDED_VALUES_AFTER_VALIDATION = [
        self::GROWTYPE_FORM_SUBMITTER_ID,
        self::GROWTYPE_FORM_NAME_IDENTIFICATOR,
        self::GROWTYPE_FORM_POST_IDENTIFICATOR,
        self::GROWTYPE_QUIZ_UNIQUE_HASH
    ];

    const GROWTYPE_FORM_SUBMITTER_ID = 'form_submitter_id';
    const GROWTYPE_FORM_NAME_IDENTIFICATOR = 'growtype_form_name';
    const GROWTYPE_FORM_POST_IDENTIFICATOR = 'growtype_form_post_id';
    const GROWTYPE_FORM_REDIRECT_AFTER = 'growtype_form_redirect_after';
    const GROWTYPE_QUIZ_UNIQUE_HASH = 'growtype_quiz_unique_hash';
    const GROWTYPE_FORM_SPAM_IDENTIFICATOR = 'email_spam';
    const GROWTYPE_FORM_ALLOWED_SUBMIT_ACTIONS = ['submit', 'preview', 'save_as_draft', 'delete', 'update'];
    const GROWTYPE_FORM_SUBMIT_ACTION = 'growtype_form_submit_action';
    const EXCLUDED_VALUES_FROM_SAVING = [
        'password',
        'repeat_password',
        'submit',
        self::GROWTYPE_FORM_REDIRECT_AFTER,
        self::GROWTYPE_FORM_SUBMIT_ACTION,
        self::GROWTYPE_FORM_SPAM_IDENTIFICATOR,
        self::GROWTYPE_FORM_SUBMITTER_ID
    ];

    const EXCLUDED_VALUES_FROM_RETURN = ['password', 'repeat_password'];

    const ALTERNATIVE_SUBMITTED_DATA_KEYS = [
        'name' => 'name_s'
    ];

    /**
     * Process data
     */
    public function __construct()
    {
        if (!is_admin()) {
            add_filter('wp_loaded', array ($this, 'growtype_form_process_posted_data'));
        }

        /**
         * Auth
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/crud/auth/class-growtype-form-facebook.php';
        new Growtype_Form_Facebook();

        include_once GROWTYPE_FORM_PATH . 'includes/methods/crud/auth/class-growtype-form-google.php';
        new Growtype_Form_Google();
    }

    /**
     * @return void
     */
    function growtype_form_process_posted_data()
    {
        /**
         * Process posted values
         */
        if (isset($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION]) && in_array(sanitize_text_field($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION]), self::GROWTYPE_FORM_ALLOWED_SUBMIT_ACTIONS)) {
            if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'delete') {
                $product_id = isset($_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR] : null;

                if (empty($product_id)) {
                    exit();
                }

                if (class_exists('woocommerce')) {
                    $product = wc_get_product($product_id);

                    if (!empty($product)) {
                        $product->delete();
                    }
                }

                $redirect_url = growtype_form_redirect_url_after_product_creation();
            } else {
                $form_name = isset($_POST[self::GROWTYPE_FORM_NAME_IDENTIFICATOR]) ? sanitize_text_field($_POST[self::GROWTYPE_FORM_NAME_IDENTIFICATOR]) : null;

                /**
                 * Check if main values are not empty
                 */
                if (empty($form_name)) {
                    throw new Exception('Empty form name');
                }

                /**
                 * Check if form is spam
                 */
                if (isset($_POST[Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATOR]) && !empty($_POST[Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATOR])) {
                    throw new Exception('Spam identification');
                }

                $submitted_values = [
                    'files' => $_FILES,
                    'data' => $_POST,
                ];

                $redirect_url = $this->process_form_submitted_values($form_name, $submitted_values);

                if (empty($redirect_url)) {
                    return __("Something went wrong. Please contact administrator.", "growtype-form");
                }
            }

            wp_redirect($redirect_url);
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
         * Set redirect url
         */
        if (isset($submitted_values['data']['growtype_form_redirect_after']) && !empty($submitted_values['data']['growtype_form_redirect_after'])) {
            $redirect_url = $submitted_values['data']['growtype_form_redirect_after'];
        } elseif (isset($_COOKIE['growtype_form_redirect_after'])) {
            $redirect_url = $_COOKIE['growtype_form_redirect_after'];
        } elseif (isset($_GET['redirect_after']) && !empty($_GET['redirect_after'])) {
            $redirect_url = $_GET['redirect_after'];
        }

        /**
         * Get form data
         */
        $form_data = self::get_growtype_form_data($form_name);

        if (empty($form_data)) {
            return null;
        }

        /**
         * Validate if submitted values match available values
         */
        $submitted_values_sanitized = $this->sanitize_form_submitted_values($form_data, $submitted_values);
        $submitted_data = $submitted_values_sanitized;

        if (!empty($submitted_data)) {
            $success_message = isset($form_data['success_message']) ? $form_data['success_message'] : '';
            $submit_action = isset($submitted_values['data'][self::GROWTYPE_FORM_SUBMIT_ACTION]) ? $submitted_values['data'][self::GROWTYPE_FORM_SUBMIT_ACTION] : 'submit';

            if (str_contains($form_name, 'signup')) {
                $child_user = isset($form_data['child_user']) && $form_data['child_user'] ? true : false;

                $signup_params = [
                    'child_user' => $child_user,
                    'username_prefix' => isset($form_data['username_prefix']) ? $form_data['username_prefix'] : '',
                ];

                $submit_data = $this->save_submitted_signup_data($submitted_data, $signup_params, $submit_action);

                if (isset($submit_data['success']) && $submit_data['success']) {
                    $user_id = $submit_data['user_id'];
                    $user = get_user_by('id', $user_id);

                    if ($user) {
                        if (!$child_user && !is_user_logged_in()) {
                            growtype_form_login_user($user_id);
                        }

                        if (!empty($redirect_url)) {
                            $redirect_url = $_GET['redirect_after'];
                        } else {
                            if ($submit_action === 'update') {
                                $redirect_url = home_url(growtype_form_get_url_path());
                            } else {
                                $redirect_url = growtype_form_redirect_url_after_signup();
                            }
                        }
                    }
                }
            } elseif (str_contains($form_name, 'wc_product')) {
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

                /**
                 * Check if product is set
                 */
                $submit_data = $this->create_or_update_product($product_data);

                /**
                 * Status
                 */
                if ($submit_data['success']) {
                    $product_id = $submit_data['product_id'];

                    $submit_data['product_id'] = $product_id;
                    $submit_data['success'] = true;
                    $submit_data['messages'] = isset($success_message) ? $success_message : __('Product uploaded.', 'growtype-form');

                    $redirect_url = growtype_form_redirect_url_after_product_creation();

                    if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'preview') {
                        $redirect_url = Growtype_Wc_Product::preview_permalink($product_id);
                    }

                    if (!empty($redirect_url)) {
                        return $redirect_url;
                    }
                }
            } else {
                if (isset($form_data['type']) && $form_data['type'] === 'custom') {
                    $submit_data = apply_filters('growtype_form_upload_post_custom', $form_data, $submitted_values);
                } else {
                    $submit_data = $this->upload_post($form_data, $submitted_values);

                    /**
                     * Process files
                     */
                    if (isset($submitted_values['files']) && isset($submit_data['post_id'])) {
                        /**
                         * Attach featured image
                         */
                        if (isset($submitted_values['files']['featured_image'])) {
                            $this->post_attach_featured_image($submit_data['post_id'], $submitted_values['files']['featured_image']);
                        } else {
                            $this->post_attach_files($submit_data['post_id'], $submitted_values['files']);
                        }
                    }
                }

                /**
                 * Success
                 */
                if (isset($submit_data['success']) && $submit_data['success']) {
                    if (isset($submit_data['post_id'])) {
                        $post = get_post($submit_data['post_id']);

                        if (!empty($post)) {
                            $email_content = $post->post_content;
                            $email_content = $email_content . '<br><br><p><a href="' . get_edit_post_link($post->ID) . '" target="_blank">' . __('View submission', 'growtype-form') . ' </a></p>';

                            self::send_email_to_admin($email_content, $form_data);
                        }
                    }

                    do_action('growtype_form_submit_data_success', $submit_data, $submitted_data, $form_data);

                    $submit_data['success'] = true;
                    $submit_data['messages'] = isset($submit_data['messages']) ? $submit_data['messages'] : __('Record created successfully.', 'growtype-form');
                } else {
                    $submit_data['success'] = false;
                    $submit_data['messages'] = isset($submit_data['messages']) ? $submit_data['messages'] : __('Something went wrong. Please contact site admin.', 'growtype-form');
                }
            }
        } else {
            error_log(print_r(sprintf("Growtype Form. VALIDATION FAILED. Empty response. Url: %s", isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''), true));

            $submit_data['success'] = false;
            $submit_data['messages'] = __('Please fill all required fields.', 'growtype-form');
        }

        /**
         * Set return values
         */
        if ($submit_data['success'] === false) {
            $return_values = [];
            foreach ($submitted_data as $key => $value) {
                if (!in_array($key, self::EXCLUDED_VALUES_FROM_RETURN) && !in_array($key, self::EXCLUDED_VALUES_FROM_VALIDATION)) {
                    $return_values[$key] = $value;
                }
            }

            if (!empty($return_values) && str_contains($form_name, 'signup')) {
                setcookie('signup_data', json_encode($return_values), time() + 2, COOKIEPATH, COOKIE_DOMAIN);
            }
        }

        /**
         * Prepare session redirect details
         */
        $this->growtype_form_set_notice(
            isset($submit_data['messages']) ? $submit_data['messages'] : [
                __("Something went wrong. Please contact administrator.", "growtype-form")
            ],
            ($submit_data['success'] ? 'success' : 'error')
        );

        /**
         * Redirect url
         */
        $return_page_path = home_url();

        if (isset($_SERVER['REQUEST_URI'])) {
            $return_page_path = growtype_form_get_url_path();
        }

        /**
         * Current post id
         */
        $post_id = isset($_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR] : null;

        if (!isset($redirect_url)) {
            $redirect_url = !empty($post_id) ? get_permalink($post_id) : home_url($return_page_path);
        }

        if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'preview' && class_exists('Growtype_Product')) {
            $redirect_url = Growtype_Wc_Product::edit_permalink($post_id);
        }

        return apply_filters('growtype_form_submitted_values_redirect_url', $redirect_url, $form_data);
    }

    public static function send_email_to_admin($submitted_content, $form_data)
    {
        $growtype_form_post_default_email_to = get_option('growtype_form_post_default_email_to');
        $growtype_form_post_default_email_to_subject = get_option('growtype_form_post_default_email_to_subject');
        $growtype_form_post_default_email_to_content = get_option('growtype_form_post_default_email_to_content');

        if (empty($growtype_form_post_default_email_to)) {
            return null;
        }

        $to = $growtype_form_post_default_email_to;
        $subject = $growtype_form_post_default_email_to_subject;
        $headers = array ('Content-Type: text/html; charset=UTF-8');
        $admin_email = get_option('admin_email');

        $email_body = str_replace('{post_content}', $submitted_content, $growtype_form_post_default_email_to_content);

        if (isset($form_data['form_name'])) {
            $email_body = str_replace('{form_name}', $form_data['form_name'], $email_body);
        }

        $headers[] = 'From: Admin <' . $admin_email . '>';

        /**
         * Debug
         */
        error_log(sprintf("Growtype Form. Sending email to admin. Details: %s", print_r([$to, $subject, $email_body, $headers], true)));

        wp_mail($to, $subject, $email_body, $headers);
    }

    /**
     * @param $form_name
     * @return mixed|null
     */
    public static function get_growtype_form_data($form_name)
    {
        if (empty($form_name)) {
            return null;
        }

        /**
         * Form Settings
         */
        if (str_contains($form_name, 'wc_product')) {
            $form_json_content = get_option('growtype_form_wc_product_json_content');
        } elseif (str_contains($form_name, 'signup')) {
            $form_json_content = get_option('growtype_form_signup_json_content');
        } elseif (str_contains($form_name, 'login')) {
            $form_json_content = get_option('growtype_form_login_json_content');
        } else {
            $form_json_content = get_option('growtype_form_post_json_content');
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

        $form_data = $available_forms[$form_name] ?? null;

        if (empty($form_data)) {
            return null;
        }

        /**
         * Include form name
         */
        if (!isset($form_data['form_name'])) {
            $form_data['form_name'] = $form_name;
        }

        return apply_filters('growtype_form_get_growtype_form_data', $form_data, $form_name);
    }

    /**
     * @param $data
     * @return array
     */
    public function save_submitted_signup_data($data, $signup_params, $submit_action = 'submit')
    {
        $email = isset($data['email']) ? sanitize_text_field($data['email']) : null;
        $username = isset($data['username']) ? sanitize_text_field($data['username']) : null;
        $username = !empty($username) ? $username : $email;
        $password = isset($data['password']) ? sanitize_text_field($_REQUEST['password']) : null;
        $repeat_password = isset($data['repeat_password']) ? sanitize_text_field($_REQUEST['repeat_password']) : null;

        if (!empty($signup_params['username_prefix'])) {
            $username = $signup_params['username_prefix'] . date_timestamp_get(date_create());
        }

        if ($submit_action === 'submit') {
            if (empty($username) || empty($password) || empty($email)) {
                $response['success'] = false;
                $response['messages'] = __("Missing values required for login.", "growtype-form");
                return $response;
            }
        }

        if (!empty($repeat_password)) {
            if ($password !== $repeat_password) {
                $response['success'] = false;
                $response['messages'] = __("Passwords do not match", "growtype-form");
                return $response;
            }
        }

        if (!empty($password)) {
            $validate_password = $this->validate_password($password);

            if ($validate_password['success'] === false) {
                $response['success'] = $validate_password['success'];
                $response['messages'] = $validate_password['messages'];
                return $response;
            }
        }

        /**
         * Save with unique email. Check if username is provided and email already exists in database.
         */
        if (!empty($username) && $username !== $email && email_exists($email)) {
            $email_exploded = explode('@', $email);
            $username_formatted = urlencode(str_replace(' ', '', $username));
            $email = $email_exploded[0] . '+' . $username_formatted . '@' . $email_exploded[1];
            $use_alternative_email = true;
        }

        if ($submit_action === 'submit') {
            $create_user = Growtype_Form_Signup::create_user($username, $password, $email);
        } elseif ($submit_action === 'update') {
            $user_id = get_current_user_id();

            if (empty($user_id)) {
                $response['success'] = false;
                $response['messages'] = __("Please login to update your information.", "growtype-form");
                return $response;
            }

            /**
             * Update email
             */
            if (!empty($email)) {
                $update_user = wp_update_user([
                    'ID' => $user_id,
                    'user_email' => $email
                ]);

                if (is_wp_error($update_user)) {
                    $response['success'] = false;

                    if (isset($update_user->errors['existing_user_email'])) {
                        $response['messages'] = __("Please use another email.", "growtype-form");
                    } else {
                        $response['messages'] = __("Something went wrong.", "growtype-form");
                    }

                    return $response;
                }
            }

            /**
             * Update password
             */
            if (!empty($password)) {
                wp_set_password($password, $user_id);
            }

            /**
             * Update email
             */
            if (!empty($username)) {
                if (username_exists($username)) {
                    $user = get_user_by('login', $username);
                    if ($user->ID !== $user_id) {
                        $response['success'] = false;
                        $response['messages'] = __("Unfortunately, you cannot use this username.", "growtype-form");
                        return $response;
                    }
                } else {
                    global $wpdb;

                    $wpdb->update(
                        $wpdb->users,
                        ['user_login' => $username],
                        ['ID' => $user_id]
                    );

                    if (is_wp_error($update_user)) {
                        $response['success'] = false;

                        if (isset($update_user->errors['existing_user_email'])) {
                            $response['messages'] = __("Please use another email.", "growtype-form");
                        } else {
                            $response['messages'] = __("Something went wrong.", "growtype-form");
                        }

                        return $response;
                    }
                }
            }

            $create_user = [
                'user_id' => get_current_user_id(),
                'success' => true
            ];
        }

        /**
         * Return response
         */
        if ($create_user['success'] === false) {
            $response['success'] = false;
            $response['messages'] = $create_user['messages'];
        } else {
            $user_id = $create_user['user_id'];

            if (isset($use_alternative_email) && $use_alternative_email) {
                update_user_meta($user_id, 'use_alternative_email', true);
            }

            /**
             * Save child user parameters
             */
            if (isset($signup_params['child_user']) && $signup_params['child_user']) {
                /**
                 * Set parent users
                 */
                $parent_user_ids = get_user_meta($user_id, 'parent_user_ids', true);

                if (empty($parent_user_ids)) {
                    $parent_user_ids = [get_current_user_id()];
                } else {
                    array_push($parent_user_ids, get_current_user_id());
                }

                update_user_meta($user_id, 'parent_user_ids', $parent_user_ids);

                /**
                 * Set child users
                 */
                $child_user_ids = get_user_meta(get_current_user_id(), 'child_user_ids', true);

                if (empty($child_user_ids)) {
                    $child_user_ids = [$user_id];
                } else {
                    array_push($child_user_ids, $user_id);
                }

                update_user_meta(get_current_user_id(), 'child_user_ids', $child_user_ids);
            }

            $response = $this->update_user_meta_details($user_id, $data);

            do_action('growtype_form_update_signup_user_data', $user_id, $data, $submit_action);

            if ($response['success']) {
                $response['messages'] = __("Sign up successful.", "growtype-form");

                if ($submit_action === 'update') {
                    $response['messages'] = __("The information was successfully updated.", "growtype-form");
                }
            } else {
                $response['messages'] = __("Something went wrong.", "growtype-form");
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

        /**
         * Collect fields
         */
        foreach ($form_data as $key => $field_group) {
            if (str_contains('main_fields', $key) || str_contains('confirmation_fields', $key)) {
                array_push($available_fields, array_column($field_group, 'name'));
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
            /**
             * Exlude certain values
             */
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
                $key = self::ALTERNATIVE_SUBMITTED_DATA_KEYS[$key];
            }

            $submitted_values_sanitized[$key] = $value;
        }

        /**
         * Set confirmation fields values
         */
        if (isset($form_data['confirmation_fields']) && !empty($form_data['confirmation_fields'])) {
            foreach ($form_data['confirmation_fields'] as $field) {
                if (isset($submitted_values_sanitized[$field['name']])) {
                    if ($field['type'] === 'checkbox') {
                        $submitted_values_sanitized[$field['name']] = 'true';
                    }
                } else {
                    if ($field['type'] === 'checkbox') {
                        $submitted_values_sanitized[$field['name']] = 'false';
                    }
                }
            }
        }

        /**
         * Recheck values which did not pass initial validation. Used for repeater fields check.
         */
        if (!empty($submitted_values_notsanitized)) {
            $passed_values = [];
            foreach ($submitted_values_notsanitized as $index => $value) {
                $match = array_filter($required_fields_names, function ($key) use ($value) {
                    return str_contains($key, $value);
                });

                if (!$match) {
                    $match = array_filter($required_fields_names, function ($key) use ($value) {
                        return str_contains($value, $key);
                    });
                }

                if ($match) {
                    array_push($passed_values, $value);
                }
            }

            if ($passed_values !== $submitted_values_notsanitized) {
                return null;
            }
        }

        /**
         * Extra values included in validation
         */
        foreach (self::get_included_values_after_validation() as $value) {
            if (isset($submitted_values['data'][$value]) && !isset($submitted_values_sanitized[$value])) {
                $submitted_values_sanitized[$value] = $submitted_values['data'][$value];
            }
        }

        return $submitted_values_sanitized;
    }

    /**
     * @return mixed|null
     */
    public static function get_included_values_after_validation()
    {
        return apply_filters('growtype_form_included_values_after_validation', self::INCLUDED_VALUES_AFTER_VALIDATION);
    }

    /**
     * @param $data
     * @return array
     */
    public function update_user_meta_details($user_id, $data)
    {
        /**
         * Save extra values
         */
        foreach ($data as $key => $value) {
            if (in_array($key, self::EXCLUDED_VALUES_FROM_SAVING)) {
                continue;
            }

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

            /**
             * Sanitize value
             */
            if (is_array($value)) {
                $sanitized_value = [];
                foreach ($value as $value_key => $single_value) {
                    $sanitized_value[sanitize_text_field($value_key)] = sanitize_text_field($single_value);
                }
            } else {
                $sanitized_value = sanitize_text_field($value);
            }

            /**
             * Save value
             */
            update_user_meta($user_id, $key, $sanitized_value);
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
                $status['messages'] = __("Your Password Must Contain At Least 8 Characters!", "growtype-form");
            } elseif (!preg_match("#[0-9]+#", $password)) {
                $status['success'] = false;
                $status['messages'] = __("Your Password Must Contain At Least 1 Number!", "growtype-form");
            } elseif (!preg_match("#[A-Z]+#", $password)) {
                $status['success'] = false;
                $status['messages'] = __("Your Password Must Contain At Least 1 Capital Letter!", "growtype-form");
            } elseif (!preg_match("#[a-z]+#", $password)) {
                $status['success'] = false;
                $status['messages'] = __("Your Password Must Contain At Least 1 Lowercase Letter!", "growtype-form");
            }
        } else {
            $status['success'] = false;
            $status['messages'] = __("Please enter password.", "growtype-form");
        }

        return $status;
    }
}
