<?php

use EmailValidator\EmailValidator;

/**
 * Class Growtype_Form_Wc_Crud
 * Woocommerce crud
 */
class Growtype_Form_Crud
{
    use GrowtypeFormPost;
    use GrowtypeFormUser;
    use GrowtypeFormFile;
    use GrowtypeFormProduct;

    const URL_PATH = 'auth';
    const GROWTYPE_FORM_SUBMITTER_ID = 'form_submitter_id';
    const GROWTYPE_FORM_NONCE_KEY = 'growtype_form_nonce';
    const GROWTYPE_FORM_WP_HTTP_REFERER = '_wp_http_referer';
    const GROWTYPE_FORM_NAME_IDENTIFICATOR = 'growtype_form_name';
    const GROWTYPE_FORM_POST_IDENTIFICATOR = 'growtype_form_post_id';
    const GROWTYPE_FORM_REDIRECT_AFTER = 'growtype_form_redirect_after';
    const GROWTYPE_FORM_PURPOSE = 'growtype_form_purpose';
    const GROWTYPE_FORM_FORM_DATA = 'growtype_form_form_data';
    const GROWTYPE_QUIZ_UNIQUE_HASH = 'growtype_quiz_unique_hash';
    const GROWTYPE_FORM_LANGUAGE = 'form_language';
    const GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES = [
        [
            'key' => 'email_address_s_c',
            'type' => 'email',
            'style' => 'display:none;',
            'hidden' => false
        ],
        [
            'key' => 'important_data_s_c_h',
            'type' => 'text',
            'style' => '',
            'hidden' => true
        ]
    ];
    const GROWTYPE_FORM_ALLOWED_SUBMIT_ACTIONS = ['submit', 'preview', 'save_as_draft', 'delete', 'update'];
    const GROWTYPE_FORM_SUBMIT_ACTION = 'growtype_form_submit_action';
    const EXCLUDED_VALUES_FROM_SAVING = [
        'password',
        'repeat_password',
        'submit',
        self::GROWTYPE_FORM_REDIRECT_AFTER,
        self::GROWTYPE_FORM_SUBMIT_ACTION,
        self::GROWTYPE_FORM_SUBMITTER_ID
    ];

    const EXCLUDED_VALUES_FROM_VALIDATION = [
        self::GROWTYPE_FORM_SUBMIT_ACTION,
        self::GROWTYPE_FORM_SUBMITTER_ID,
        self::GROWTYPE_FORM_NAME_IDENTIFICATOR,
        self::GROWTYPE_FORM_POST_IDENTIFICATOR,
        self::GROWTYPE_FORM_REDIRECT_AFTER,
        self::GROWTYPE_QUIZ_UNIQUE_HASH,
        self::GROWTYPE_FORM_LANGUAGE,
        self::GROWTYPE_FORM_NONCE_KEY,
        self::GROWTYPE_FORM_WP_HTTP_REFERER,
        self::GROWTYPE_FORM_PURPOSE,
        'preloaded',
        'upload_file_order',
    ];

    const INCLUDED_VALUES_AFTER_VALIDATION = [
        self::GROWTYPE_FORM_SUBMITTER_ID,
        self::GROWTYPE_FORM_NAME_IDENTIFICATOR,
        self::GROWTYPE_FORM_PURPOSE,
        self::GROWTYPE_FORM_POST_IDENTIFICATOR,
        self::GROWTYPE_QUIZ_UNIQUE_HASH
    ];

    const EXCLUDED_VALUES_FROM_RETURN = ['password', 'repeat_password'];

    const ALTERNATIVE_SUBMITTED_DATA_KEYS = [
        'name' => 'name_s'
    ];

    const DISPOSABLE_DOMAINS = [
        'mailinator.com',
        'tempmail.com',
        '10minutemail.com',
        'yopmail.com',
        'guerrillamail.com',
        'student.pcps.us',
        'maildrop.cc',
        'discard.email',
        'fakeinbox.com',
        'trashmail.com',
        'throwawaymail.com',
        'dispostable.com',
        'getnada.com',
        'mohmal.com',
        'emailondeck.com',
        'mailnesia.com',
        'anonymmail.de',
        'airmailbox.website',
        'emailtemporario.com.br',
        'mailcatch.com',
        'inboxbear.com',
        'spambox.us',
        'sharklasers.com',
        'spambog.com',
        'temp-mail.org',
        'dropmail.me',
        'mail7.io',
        'burnermail.io',
        'jetable.org',
        'trash-mail.com',
        'mailfence.com',
        'protonmail.com', // sometimes used for temporary purposes
        'tutanota.com', // sometimes used for temporary purposes
        'luxusmail.org',
        'spam4.me',
        'mytemp.email',
        'frankfort.k12.in.us',
        'testing.com',
        'test.com',
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

    public static function get_excluded_values_from_saving()
    {
        return array_merge(self::EXCLUDED_VALUES_FROM_SAVING, array_pluck(self::GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES, 'key'));
    }

    public static function get_excluded_values_from_validation()
    {
        return array_merge(self::EXCLUDED_VALUES_FROM_VALIDATION, array_pluck(self::GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES, 'key'));
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
            // Verify the nonce
            if (!isset($_POST['growtype_form_nonce']) || !wp_verify_nonce($_POST['growtype_form_nonce'], 'growtype_form_general')) {
                error_log('Growtype Form - Nonce verification failed');
                wp_redirect(home_url());
                exit;
            }

            if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'delete') {
                $post_id = isset($_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR] : null;

                do_action('growtype_form_delete');

                if (empty($post_id)) {
                    $redirect_url = home_url();
                } else {

                    if (class_exists('Growtype_Wc_Product')) {
                        $user_has_created_product = Growtype_Wc_Product::user_has_created_product($post_id);

                        if ($user_has_created_product) {
                            $post_can_be_deleted = true;
                        }
                    }

                    $post_can_be_deleted = apply_filters('growtype_form_post_can_be_deleted', $post_can_be_deleted ?? false, $post_id);

                    if ($post_can_be_deleted) {
                        $post = get_post($post_id);

                        if (!empty($post)) {
                            if ($post->post_type === 'product' && class_exists('woocommerce')) {
                                $product = wc_get_product($post_id);

                                if (!empty($product)) {
                                    $product->delete();
                                }
                            } else {
                                wp_delete_post($post_id);
                            }
                        }
                    }

                    $redirect_url = growtype_form_redirect_url_after_product_creation();
                }
            } else {
                $form_name = isset($_POST[self::GROWTYPE_FORM_NAME_IDENTIFICATOR]) ? sanitize_text_field($_POST[self::GROWTYPE_FORM_NAME_IDENTIFICATOR]) : null;

                /**
                 * Check if main values are not empty
                 */
                if (empty($form_name)) {
                    error_log(sprintf('Growtype Form - Empty form name. Posted data %s', print_r($_POST ?? [], true)));
                    wp_redirect(home_url());
                    exit();
                }

                /**
                 * Check if submit is spam
                 */
                $submitted_data_is_free_from_spam = $this->submitted_data_is_free_from_spam($_POST);

                if (!$submitted_data_is_free_from_spam) {
                    wp_redirect(home_url());
                    exit();
                }

                /**
                 * Check throttle
                 */
                $throttle_passed = $this->check_submit_throttle($form_name);

                if ($throttle_passed === false) {
                    Growtype_Form_Notice::growtype_form_set_notice(
                        [
                            growtype_form_message('submission_throttled')
                        ],
                        'error'
                    );

                    wp_redirect(home_url(growtype_form_get_url_path()));
                    exit();
                }

                $submitted_values = [
                    'files' => $_FILES,
                    'data' => $_POST,
                ];

                $redirect_url = $this->process_form_submitted_values($form_name, $submitted_values);

                if (empty($redirect_url)) {
                    Growtype_Form_Notice::growtype_form_set_notice(
                        [
                            growtype_form_message()
                        ],
                        'error'
                    );

                    wp_redirect(growtype_form_get_url_path());
                    exit();
                }
            }

            wp_redirect($redirect_url);
            exit();
        }
    }

    public function submitted_data_is_free_from_spam($data)
    {
        foreach (self::GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES as $rule) {
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

    public function check_submit_throttle($form_name, $time = 5)
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
     * @param $form_name
     * @param $submitted_data
     * @return false|string|WP_Error
     */
    function process_form_submitted_values($form_name, $submitted_values)
    {
        /**
         * Set redirect url
         */
        $redirect_url = null;

        if (isset($submitted_values['data']['growtype_form_redirect_after']) && !empty($submitted_values['data']['growtype_form_redirect_after'])) {
            $redirect_url = $submitted_values['data']['growtype_form_redirect_after'];
        } elseif (isset($_COOKIE['growtype_form_redirect_after'])) {
            $redirect_url = $_COOKIE['growtype_form_redirect_after'];
        } elseif (isset($_GET['redirect_after']) && !empty($_GET['redirect_after'])) {
            $redirect_url = $_GET['redirect_after'];
        }

        if (!empty($redirect_url)) {
            $redirect_url = growtype_form_add_domain_to_url_if_missing($redirect_url);
        }

        /**
         * Return page path
         */
        $return_page_path = home_url();

        if (isset($_SERVER['REQUEST_URI'])) {
            $return_page_path = growtype_form_get_url_path();
        }

        /**
         * Get form data
         */

        if (isset($_POST[self::GROWTYPE_FORM_FORM_DATA]) && !empty($_POST[self::GROWTYPE_FORM_FORM_DATA])) {
            $form_data = json_decode(base64_decode($_POST[self::GROWTYPE_FORM_FORM_DATA]), true);

            unset($submitted_values['data'][self::GROWTYPE_FORM_FORM_DATA]);
        } else {
            $form_data = self::get_growtype_form_data($form_name);
        }

        if (empty($form_data)) {
            return null;
        }

        /**
         * Current post id
         */
        $post_id = isset($_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $_POST[self::GROWTYPE_FORM_POST_IDENTIFICATOR] : null;

        /**
         * Validate if submitted values match available values
         */
        $validated_form_submitted_values = $this->validate_form_submitted_values($form_data, $submitted_values);

        $submitted_data_is_valid = $validated_form_submitted_values['success'] ?? false;
        $submitted_data = $validated_form_submitted_values['data'] ?? '';

        $submitted_data_message = $validated_form_submitted_values['message'] ?? '';

        if ($submitted_data_is_valid && !empty($submitted_data)) {
            $success_message = isset($form_data['success_message']) ? $form_data['success_message'] : '';
            $submit_action = isset($submitted_values['data'][self::GROWTYPE_FORM_SUBMIT_ACTION]) ? $submitted_values['data'][self::GROWTYPE_FORM_SUBMIT_ACTION] : 'submit';

            if (strpos($form_name, 'signup') !== false) {
                $redirect_url = growtype_form_redirect_url_after_signup();

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

                        if (empty($redirect_url) && $submit_action === 'update') {
                            $redirect_url = home_url(growtype_form_get_url_path());
                        }
                    }
                }
            } elseif (class_exists('Growtype_Wc') && strpos($form_name, 'wc_product') !== false) {
                $product_data = $submitted_values;

                /**
                 * Format long description if contains multiple descriptions
                 */
                if (isset($product_data['data']['description']) && is_array($product_data['data']['description'])) {

                    $description_fields = [];
                    foreach ($form_data['main_fields'] as $field) {
                        if (strpos($field['name'], 'description') !== false) {
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
                if (isset($submit_data['success']) && $submit_data['success']) {
                    $product_id = $submit_data['product_id'];

                    $submit_data['product_id'] = $product_id;
                    $submit_data['success'] = true;
                    $submit_data['message'] = isset($success_message) ? $success_message : __('Product uploaded.', 'growtype-form');

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
                    $submit_data = apply_filters('growtype_form_upload_post_custom', $form_data, $submitted_values, $form_name);
                } else {
                    $submit_data = growtype_form_save_submission($form_data, $submitted_values);

                    /**
                     * Process files
                     */
                    if (isset($submitted_values['files']) && isset($submit_data['post_id'])) {
                        /**
                         * Attach featured image
                         */
                        if (isset($submitted_values['files']['featured_image'])) {
                            self::post_attach_featured_image($submit_data['post_id'], $submitted_values['files']['featured_image']);
                        } else {
                            self::post_attach_files($submit_data['post_id'], $submitted_values['files']);
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
                    $submit_data['message'] = isset($submit_data['message']) ? $submit_data['message'] : growtype_form_message('success');
                } else {
                    $submit_data['success'] = false;
                    $submit_data['message'] = isset($submit_data['message']) ? $submit_data['message'] : growtype_form_message();
                }
            }
        } else {
            error_log(sprintf("Growtype Form - VALIDATION FAILED. Data: %s", print_r($validated_form_submitted_values, true)));

            $submit_data['success'] = false;
            $submit_data['message'] = $submitted_data_message;
        }

        /**
         * Set redirect url
         */
        if (isset($submit_data['redirect_url']) && !empty($submit_data['redirect_url'])) {
            $redirect_url = $submit_data['redirect_url'];
        }

        /**
         * Set return values
         */
        if ($submit_data['success'] === false) {
            $redirect_url = home_url($return_page_path) . '?' . http_build_query($_GET);

            $return_values = [];
            if (!empty($submitted_data)) {
                foreach ($submitted_data as $key => $value) {
                    if (!in_array($key, self::EXCLUDED_VALUES_FROM_RETURN) && !in_array($key, self::get_excluded_values_from_validation())) {
                        $return_values[$key] = $value;
                    }
                }
            }

            if (!empty($return_values) && strpos($form_name, 'signup') !== false) {
                setcookie('signup_data', json_encode($return_values), time() + 3, COOKIEPATH, COOKIE_DOMAIN);
            }
        } else {
            if ($_POST[self::GROWTYPE_FORM_SUBMIT_ACTION] === 'preview' && class_exists('Growtype_Product')) {
                $redirect_url = Growtype_Wc_Product::edit_permalink($post_id);
            }

            if (!isset($redirect_url)) {
                $redirect_url = !empty($post_id) ? get_permalink($post_id) : home_url($return_page_path);
            }
        }

        $set_notice = $submit_data['set_notice'] ?? true;

        /**
         * Prepare session redirect details
         */
        if ($set_notice) {
            Growtype_Form_Notice::growtype_form_set_notice(
                isset($submit_data['message']) ? $submit_data['message'] : growtype_form_message(),
                ($submit_data['success'] ? 'success' : 'error')
            );
        }

        return apply_filters('growtype_form_submitted_values_redirect_url', $redirect_url, $form_data, $submitted_data, $submit_data);
    }

    public static function send_email_to_admin($submitted_content, $form_data)
    {
        $send_email_to_admin_details = [
            'recipient' => get_option('growtype_form_post_default_email_to'),
            'subject' => get_option('growtype_form_post_default_email_to_subject'),
            'content' => get_option('growtype_form_post_default_email_to_content'),
        ];

        $growtype_form_details = apply_filters('growtype_form_send_email_to_admin_details', $send_email_to_admin_details, $submitted_content, $form_data);

        $growtype_form_post_default_email_to = $growtype_form_details['recipient'];
        $growtype_form_post_default_email_to_subject = $growtype_form_details['subject'];
        $growtype_form_post_default_email_to_content = $growtype_form_details['content'];

        if (empty($growtype_form_post_default_email_to)) {
            return null;
        }

        $to = $growtype_form_post_default_email_to;
        $subject = $growtype_form_post_default_email_to_subject;
        $headers = array ('Content-Type: text/html; charset=UTF-8');
        $admin_email = get_option('admin_email');

        $email_body = str_replace('{form_submission_details}', $submitted_content, $growtype_form_post_default_email_to_content);

        if (isset($form_data['form_name'])) {
            $email_body = str_replace('{form_name}', $form_data['form_name'], $email_body);
        }

        $email_body .= '<br><strong>Submitter IP:</strong> ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');

        $headers[] = 'From: Admin <' . $admin_email . '>';

        /**
         * Debug
         */
        error_log(sprintf("Growtype Form - Sending email to admin. Details: %s", print_r([$to, $subject, $email_body, $headers], true)));

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
         * Check forms
         */
        $forms = get_posts(
            [
                'posts_per_page' => -1,
                'post_type' => Growtype_Form_Admin_Form::POST_TYPE_NAME,
                'post_status' => 'publish'
            ]
        );

        if (!empty($forms)) {
            foreach ($forms as $form) {
                if (Growtype_Form_General::format_form_name($form->post_title) === $form_name) {
                    $form_json_content = get_post_meta($form->ID, 'json_content', true);
                    $form_json_content = self::json_sanitize($form_json_content);
                    if (!empty($form_json_content)) {
                        $form_json_content = [
                            $form_name => $form_json_content
                        ];

                        $form_json_content = json_encode($form_json_content);

                        break;
                    }
                }
            }
        }

        if (empty($form_json_content)) {
            /**
             * Form Settings
             */
            if (strpos($form_name, 'wc_product') !== false) {
                $form_json_content = get_option('growtype_form_wc_product_json_content');
            } elseif (strpos($form_name, 'signup') !== false) {
                $form_json_content = get_option('growtype_form_signup_json_content');
            } elseif (strpos($form_name, 'login') !== false) {
                $form_json_content = get_option('growtype_form_login_json_content');
            } else {
                $form_json_content = get_option('growtype_form_post_json_content');
            }
        }

        if (!empty($form_json_content)) {
            $available_forms = self::json_sanitize($form_json_content);

            if (strpos($form_name, 'edit') !== false && !isset($available_forms[$form_name]['main_fields'])) {
                $form_parent = str_replace('_edit', '', $form_name);

                if (isset($available_forms[$form_parent]['main_fields'])) {
                    $available_forms[$form_name]['main_fields'] = $available_forms[$form_parent]['main_fields'];
                }
            }
        }

        $form_data = $available_forms[$form_name] ?? [];

        /**
         * Include form name
         */
        if (!empty($form_data) && !isset($form_data['form_name'])) {
            $form_data['form_name'] = $form_name;
        }

        return apply_filters('growtype_form_get_growtype_form_data', $form_data, $form_name);
    }

    public static function json_sanitize($form_json_content)
    {
        $available_forms = json_decode($form_json_content, true);

        /**
         * Check if trailing comma exists
         */
        if (empty($available_forms)) {
            $form_json_content = preg_replace('/,\s*([\]}])/m', '$1', $form_json_content);
            $available_forms = json_decode($form_json_content, true);
        }

        return $available_forms;
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

        if (!isset($data['auth_method'])) {
            $data['auth_method'] = 'basic';
        }

        if (!empty($signup_params['username_prefix'])) {
            $username = $signup_params['username_prefix'] . date_timestamp_get(date_create());
        }

        if ($submit_action === 'submit') {
            if (empty($username) || empty($password) || empty($email)) {
                $response['success'] = false;
                $response['message'] = __("Missing values required for login.", "growtype-form");
                return $response;
            }
        }

        if (!empty($repeat_password)) {
            if ($password !== $repeat_password) {
                $response['success'] = false;
                $response['message'] = __("Passwords do not match.", "growtype-form") . " ";
                return $response;
            }
        }

        if (!empty($password)) {
            $validate_password = $this->validate_password($password);

            if (filter_var($validate_password['success'], FILTER_VALIDATE_BOOLEAN) === false) {
                $response['success'] = $validate_password['success'];
                $response['message'] = $validate_password['message'];
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
                $response['message'] = __("Please login to update your information.", "growtype-form");
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
                        $response['message'] = __("Please use another email.", "growtype-form");
                    } else {
                        $response['message'] = growtype_form_message();
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
                        $response['message'] = __("Unfortunately, you cannot use this username.", "growtype-form");
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
                            $response['message'] = __("Please use another email.", "growtype-form");
                        } else {
                            $response['message'] = growtype_form_message();
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
        if (empty($create_user) || $create_user['success'] === false) {
            $response['success'] = false;
            $response['message'] = $create_user['message'] ?? growtype_form_message();
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
                $response['message'] = __("Sign up successful.", "growtype-form");

                if ($submit_action === 'update') {
                    $response['message'] = __("The information was successfully updated.", "growtype-form");
                }
            } else {
                $response['message'] = growtype_form_message();
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
    function validate_form_submitted_values($form_data, $submitted_values)
    {
        $available_fields = [];

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

            if ($passed_values !== $submitted_values_notsanitized) {
                return [
                    'success' => false,
                    'message' => __("Please fill all required fields.", "growtype-form")
                ];
            }
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
        if (isset($submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH]) && !empty($submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH])) {
            $quiz_result = Growtype_Quiz_Result_Crud::get_quiz_single_result_data_by_unique_hash($submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH]);

            if (empty($quiz_result)) {
                error_log(sprintf('Growtype Form - Wrong growtype quiz unique hash. Clear hash. Details: %s', print_r($submitted_values_sanitized, true)));

                $submitted_values_sanitized[Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH] = '';
            }
        }

        return [
            'success' => true,
            'data' => $submitted_values_sanitized
        ];
    }

    public static function validate_email($email)
    {
        $email_validation_levels = [
            'first_level' => [
                'active' => apply_filters('growtype_form_first_level_email_validation_active', true),
                'method' => 'first_level_email_validation'
            ],
            'second_level' => [
                'active' => apply_filters('growtype_form_second_level_email_validation_active', false),
                'method' => 'second_level_email_validation'
            ]
        ];

        $last_validation_result = null;
        foreach ($email_validation_levels as $email_validation_level) {
            if ($email_validation_level['active']) {
                $validation_method = $email_validation_level['method'];
                $validation_result = self::$validation_method($email);

                $last_validation_result = $validation_result;

                if ($validation_result['success'] === false && $validation_result['failed_validation'] === false) {
                    return $validation_result;
                }
            }
        }

        return $last_validation_result;
    }

    public static function first_level_email_validation($email, $config = [])
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

        $default_config = [
            'checkMxRecords' => true,
            'checkBannedListedEmail' => true,
            'checkDisposableEmail' => true,
            'checkFreeEmail' => false,
            'bannedList' => [],
            'disposableList' => Growtype_Form_Crud::DISPOSABLE_DOMAINS,
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

    public static function second_level_email_validation($email)
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
            if (in_array($key, self::get_excluded_values_from_saving())) {
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
         * Return response
         */
        $response['user_id'] = $user_id;
        $response['success'] = true;

        return $response;
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
    function validate_password($password)
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
}
