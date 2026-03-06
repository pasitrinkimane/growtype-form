<?php

use EmailValidator\EmailValidator;

class Growtype_Form_Crud_Validation
{
    public static function validate_email($email)
    {
        $email_validation_levels = [
            'disposable_check' => [
                'active' => apply_filters('growtype_form_disposable_email_validation_active', true),
                'method' => 'disposable_email_validation'
            ],
            'basic_check' => [
                'active' => apply_filters('growtype_form_basic_email_validation_active', false),
                'method' => 'basic_email_validation'
            ],
            'external_usercheck' => [
                'active' => apply_filters('growtype_form_external_usercheck_email_validation_active', false),
                'method' => 'external_usercheck_email_validation'
            ]
        ];

        $all_success = true;
        foreach ($email_validation_levels as $email_validation_level) {
            if ($email_validation_level['active']) {
                $validation_method = $email_validation_level['method'];
                $validation_result = self::$validation_method($email);

                if ($validation_result['success'] === false) {
                    return $validation_result;
                }
            }
        }

        return [
            'success' => true,
            'failed_validation' => false
        ];
    }

    public static function disposable_email_validation($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        $disposable_domains = apply_filters('growtype_form_disposable_domains', Growtype_Form_Crud::DISPOSABLE_DOMAINS);

        if (in_array(strtolower($domain), array_map('strtolower', $disposable_domains))) {
            return [
                'failed_validation' => false,
                'success' => false,
                'message' => __("Disposable email addresses are not allowed.", "growtype-form"),
            ];
        }

        return [
            'failed_validation' => false,
            'success' => true,
        ];
    }

    public static function basic_email_validation($email, $config = [])
    {
        // Validate email format using filter_var
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'failed_validation' => false,
                'success' => false,
                'message' => __("Invalid email format.", "growtype-form"),
            ];
        }

        return [
            'failed_validation' => false,
            'success' => true,
        ];
    }

    public static function plugin_email_validation($email, $config = [])
    {
        $default_config = [
            'checkMxRecords' => true,
            'checkBannedListedEmail' => true,
            'checkDisposableEmail' => true,
            'checkFreeEmail' => false,
            'bannedList' => [],
            'disposableList' => apply_filters('growtype_form_disposable_domains', Growtype_Form_Crud::DISPOSABLE_DOMAINS),
            'freeList' => [],
        ];

        $config = array_merge($default_config, $config);

        $emailValidator = new EmailValidator($config);

        $isValid = $emailValidator->validate($email);

        if (!$isValid) {
            $message = $emailValidator->getErrorReason();

            error_log(sprintf('Growtype Form - Email validation failed. Email: %s. Reason: %s', $email, $message));

            return [
                'failed_validation' => false,
                'success' => false,
                'message' => __("Please use a valid email address.", "growtype-form"),
            ];
        }

        return [
            'failed_validation' => false,
            'success' => true,
        ];
    }

    public static function external_usercheck_email_validation($email)
    {
        $url = "https://api.usercheck.com/email/" . urlencode($email);

        // Initialize cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 2); // Set timeout to 2 seconds

        $response = curl_exec($curl);

        // Check for cURL errors or timeout
        if (curl_errno($curl)) {
            curl_close($curl);

            return [
                'failed_validation' => true,
                'success' => false,
            ];
        }

        curl_close($curl);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Check if the API responded with valid information
        if (isset($data['status']) && $data['status'] == 200 && isset($data['mx']) && $data['mx'] === true && isset($data['disposable']) && $data['disposable'] === false) {
            return [
                'failed_validation' => false,
                'success' => true,
            ];
        } else {
            return [
                'failed_validation' => false,
                'success' => false,
                'message' => __("The email address you entered is not valid. Please check and try again.", "growtype-form"),
            ];
        }
    }

    public static function simple_password_is_allowed()
    {
        return get_option('growtype_form_allow_simple_password');
    }

    /**
     * @param $password
     * @return array
     * Validate password
     */
    public static function validate_password($password)
    {
        $status['success'] = true;

        $validation_messages = self::validation_messages();

        if (!empty($password)) {
            if (self::simple_password_is_allowed()) {
                return $status;
            }

            if (strlen($password) < self::password_min_length()) {
                $status['success'] = false;
                $status['message'] = $validation_messages['password_min_length'];
            } elseif (!preg_match("#[0-9]+#", $password)) {
                $status['success'] = false;
                $status['message'] = $validation_messages['password_contains_number'];
            } elseif (!preg_match("#[A-Z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = $validation_messages['password_contains_uppercase'];
            } elseif (!preg_match("#[a-z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = $validation_messages['password_contains_lowercase'];
            }
        } else {
            $status['success'] = false;
            $status['message'] = $validation_messages['password_required'];
        }

        return $status;
    }

    public static function password_min_length()
    {
        return 8;
    }

    public static function validation_messages()
    {
        $validation_messages = [
            'at_leas_one_selection' => __('At least one selection is required.', 'growtype-form'),
            'wrong_date_format' => __('Wrong date format. Please select again.', 'growtype-form'),
            'password_min_length' => sprintf(__("Your password must contain at least %s characters.", "growtype-form"), self::password_min_length()),
            'password_required' => __("Please enter password.", "growtype-form"),
            'password_contains_number' => __("Your password must contain at least 1 number.", "growtype-form"),
            'password_contains_uppercase' => __("Your password must contain at least 1 capital letter.", "growtype-form"),
            'password_contains_lowercase' => __("Your password must contain at least 1 lowercase letter.", "growtype-form"),
            'repeat_password' => __("Please repeat your password.", "growtype-form"),
            'passwords_not_match' => __("Passwords do not match.", "growtype-form"),
        ];

        return apply_filters('growtype_form_validation_messages', $validation_messages);
    }

    public static function get_included_values_after_validation()
    {
        return apply_filters('growtype_form_included_values_after_validation', Growtype_Form_Crud::INCLUDED_VALUES_AFTER_VALIDATION);
    }

    public static function get_excluded_values_from_validation()
    {
        return array_merge(Growtype_Form_Crud::EXCLUDED_VALUES_FROM_VALIDATION, array_column(Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES,
            'key'));
    }

    public static function submitted_data_is_free_from_spam($data)
    {
        foreach (Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES as $rule) {
            $value = $data[$rule['key']] ?? '';

            if (!empty($value)) {
                $ip_address = growtype_form_get_user_ip_address();

                error_log(sprintf('Growtype Form - SPAM submission. Data %s', json_encode([
                    $ip_address,
                    $data
                ])));

                return false;
            }
        }

        return true;
    }

    public static function check_submit_throttle($form_name, $time = 5)
    {
        if (is_user_logged_in()) {
            return true;
        }

        $form_name = sanitize_key($form_name);
        $user_ip = sanitize_text_field(growtype_form_get_user_ip_address());
        $user_id = get_current_user_id();

        // Generate a unique cache key
        $cache_key = 'gt_form_last_submit_' . md5($form_name . '_' . ($user_id ?: $user_ip));

        // Check if user submitted recently
        if (get_transient($cache_key)) {
            error_log(sprintf(
                'Growtype Form - WP throttle applied for form "%s". Time limit: %s seconds. User: %s',
                $form_name,
                $time,
                $user_id ?: $user_ip
            ));
            return false;
        }

        // Set cooldown period
        set_transient($cache_key, time(), $time);

        return true;
    }

    /**
     * @param $fields
     * @param $posted_data
     * @return array
     * Map post fields with shortcode fields
     */
    public static function validate_form_submitted_values($form_data, $submitted_values)
    {
        $available_fields = [];
        $required_fields_names = [];

        /**
         * Collect fields
         */
        foreach ($form_data as $key => $field_group) {
            if (strpos('main_fields', $key) !== false || strpos('confirmation_fields', $key) !== false) {
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

        /**
         * Validate email
         */
        if (isset($submitted_data['email']) && !empty($submitted_data['email'])) {
            $email_validation = self::validate_email($submitted_data['email']);

            if (isset($email_validation['success']) && $email_validation['success'] === false) {
                $email_validation['email'] = $submitted_data['email'];
                $email_validation['success'] = false;

                return $email_validation;
            }
        }

        foreach ($submitted_data as $key => $value) {
            /**
             * Exlude certain values
             */
            if (in_array($key, self::get_excluded_values_from_validation())) {
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
                $key = Growtype_Form_Crud::ALTERNATIVE_SUBMITTED_DATA_KEYS[$key];
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
                    return strpos($key, $value) !== false;
                });

                if (!$match) {
                    $match = array_filter($required_fields_names, function ($key) use ($value) {
                        return strpos($value, $key) !== false;
                    });
                }

                if ($match) {
                    array_push($passed_values, $value);
                }
            }

            $passed_values = array_values(array_unique($passed_values));
        }

        /**
         * Include passed values
         */
        if (!empty($passed_values)) {
            foreach ($passed_values as $key) {
                $submitted_values_sanitized[$key] = $submitted_data[$key];
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

        if (empty($submitted_values_sanitized)) {
            return [
                'success' => false,
                'message' => __("Empty submitted values.", "growtype-form")
            ];
        }

        /**
         * Reset growtype quiz unique hash
         */
        if (isset($submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH]) &&
            !empty($submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH])) {
            $quiz_result =
                Growtype_Quiz_Result_Crud::get_quiz_single_result_data_by_unique_hash($submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH]);

            if (empty($quiz_result)) {
                error_log(sprintf('Growtype Form - Wrong growtype quiz unique hash. Clear hash. Details: %s',
                    print_r($submitted_values_sanitized, true)));

                $submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH] = '';
            }
        }

        return [
            'success' => true,
            'data' => $submitted_values_sanitized
        ];
    }
}
