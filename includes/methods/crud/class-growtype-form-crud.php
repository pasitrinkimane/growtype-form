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
        'preloaded',
    ];

    const GROWTYPE_FORM_SUBMITTER_ID = 'form_submitter_id';

    const GROWTYPE_FORM_NAME_IDENTIFICATOR = 'growtype_form_name';
    const GROWTYPE_FORM_POST_IDENTIFICATOR = 'growtype_form_post_id';
    const GROWTYPE_FORM_SPAM_IDENTIFICATOR = 'email_spam';

    const GROWTYPE_FORM_ALLOWED_SUBMIT_ACTIONS = ['submit', 'preview', 'save_as_draft', 'delete'];

    const GROWTYPE_FORM_SUBMIT_ACTION = 'growtype_form_submit_action';

    const EXCLUDED_VALUES_FROM_SAVING = [
        'username',
        'password',
        'repeat_password',
        'email',
        'submit',
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
                $post_identificator = isset($_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR] : null;

                /**
                 * Check if main values are not empty
                 */
                if (empty($form_name) || empty($post_identificator)) {
                    exit();
                }

                /**
                 * Check if form is spam
                 */
                if (isset($_POST[Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATOR]) && !empty($_POST[Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATOR])) {
                    exit();
                }

                $submitted_values = [
                    'files' => $_FILES,
                    'data' => $_POST,
                ];

                $redirect_url = $this->process_form_submitted_values($form_name, $submitted_values);
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
        $submitted_data = $submitted_values['data'];

        if (!empty($submitted_values_sanitized)) {

            $success_message = $form_data['success_message'] ?? null;

            if (str_contains($form_name, 'signup')) {
                $child_user = isset($form_data['child_user']) && $form_data['child_user'] ? true : false;

                $signup_params = [
                    'child_user' => $child_user,
                    'username_prefix' => $form_data['username_prefix'] ?? null,
                ];

                $submit_data = $this->save_submitted_signup_data($submitted_data, $signup_params);

                if (isset($submit_data['success']) && $submit_data['success']) {
                    $user_id = $submit_data['user_id'];
                    $user = get_user_by('id', $user_id);

                    if ($user) {

                        if (!$child_user) {
                            wp_set_current_user($user_id, $user->user_login);
                            wp_set_auth_cookie($user_id);
                            do_action('wp_login', $user->user_login, $user);
                        }

                        if (!growtype_form_redirect_url_after_signup()) {
                            error_log('Redirect url is missing. growtype-form');
                            return __("Something went wrong. Please contact administrator.", "growtype-form");
                        }

                        return growtype_form_redirect_url_after_signup();
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
                    $submit_data['message'] = $success_message ?? __('Product uploaded.', 'growtype-form');

                    $redirect_url = growtype_form_redirect_url_after_product_creation();

                    if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'preview') {
                        $redirect_url = Growtype_Product::preview_permalink($product_id);
                    }

                    if (!empty($redirect_url)) {
                        return $redirect_url;
                    }
                }
            } elseif (str_contains($form_name, 'post')) {
                if (isset($form_data['type']) && $form_data['type'] === 'custom') {
                    $submit_data = apply_filters('growtype_form_upload_post_custom', $form_data, $submitted_values);
                } else {
                    $submit_data = $this->upload_post($form_data, $submitted_values);

                    /**
                     * Process files
                     */
                    if (isset($submitted_values['files'])) {
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
                if ($submit_data['success']) {

                    if (isset($submit_data['post_id'])) {
                        $post = get_post($submit_data['post_id']);

                        $email_content = $post->post_content;
                        $email_content = $email_content . '<br><br><p><a href="' . get_edit_post_link($post->ID) . '" target="_blank">View post</a></p>';

                        self::send_email_to_admin($email_content);
                    }

                    $submit_data['success'] = true;
                    $submit_data['message'] = isset($submit_data['message']) ? $submit_data['message'] : __('Record created successfully.', 'growtype-form');
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
         * Set return values
         */
        if ($submit_data['success'] === false) {
            $return_values = [];
            foreach ($submitted_data as $key => $value) {
                if (!in_array($key, self::EXCLUDED_VALUES_FROM_RETURN) && !in_array($key, self::EXCLUDED_VALUES_FROM_VALIDATION)) {
                    $return_values[$key] = $value;
                }
            }

            setcookie('signup_data', json_encode($return_values), time() + 1, home_url());
        }

        /**
         * Prepare session redirect details
         */
        $this->growtype_form_set_notice($submit_data['message'] ?? __("Something went wrong. Please contact administrator.", "growtype-form"), ($submit_data['success'] ? 'success' : 'error'));

        /**
         * Redirect url
         */

        $current_slug = isset($_SERVER['REQUEST_URI']) ? str_replace('/', '', $_SERVER['REQUEST_URI']) : '';

        /**
         * Current post id
         */
        $post_id = isset($_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR] : null;

        $redirect_url = !empty($post_id) ? get_permalink($post_id) : home_url($current_slug);

        if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'preview' && class_exists('Growtype_Product')) {
            $redirect_url = Growtype_Product::edit_permalink($post_id);
        }

        return $redirect_url;
    }

    public static function send_email_to_admin($submitted_content)
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

        $email_body = str_replace('$post_content', $submitted_content, $growtype_form_post_default_email_to_content);

        $headers[] = 'From: Admin <' . $admin_email . '>';

        wp_mail($to, $subject, $email_body, $headers);
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
    public function save_submitted_signup_data($data, $signup_params)
    {
        $email = isset($data['email']) ? sanitize_text_field($data['email']) : null;
        $username = isset($data['username']) ? sanitize_text_field($data['username']) : null;
        $username = !empty($username) ? $username : $email;
        $password = isset($data['password']) ? sanitize_text_field($_REQUEST['password']) : null;
        $repeat_password = isset($data['repeat_password']) ? sanitize_text_field($_REQUEST['repeat_password']) : null;

        if (!empty($signup_params['username_prefix'])) {
            $username = $signup_params['username_prefix'] . date_timestamp_get(date_create());
        }

        if (empty($email) && is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $email = $current_user->user_email;
        }

        if (empty($username) || empty($password) || empty($email)) {
            $response['success'] = false;
            $response['message'] = __("Missing values required for login.", "growtype-form");
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

            /**
             * Save child user parameters
             */
            if ($signup_params['child_user']) {
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

            $response = $this->update_user_data($user_id, $data);

            apply_filters('growtype_form_update_signup_user_data', $user_id, $data);

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
         * Recheck values which did not passed initial validation. Used for repeater fields check.
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

        return $submitted_values_sanitized;
    }

    /**
     * @param $data
     * @return array
     */
    function update_user_data($user_id, $data)
    {
        /**
         * Save extra values
         */
        foreach ($data as $key => $value) {
            if ((!is_array($value) && str_contains($value, 'password')) || empty($value) || in_array($key, self::EXCLUDED_VALUES_FROM_SAVING)) {
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
                $status['message'] = __("Your Password Must Contain At Least 8 Characters!", "growtype-form");
            } elseif (!preg_match("#[0-9]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Number!", "growtype-form");
            } elseif (!preg_match("#[A-Z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Capital Letter!", "growtype-form");
            } elseif (!preg_match("#[a-z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Lowercase Letter!", "growtype-form");
            }
        } else {
            $status['success'] = false;
            $status['message'] = __("Please enter password.", "growtype-form");
        }

        return $status;
    }
}
