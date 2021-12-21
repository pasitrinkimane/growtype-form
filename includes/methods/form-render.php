<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Render
{
    use FrontendForm;
    use FrontendSignup;

    const GROWTYPE_FORM_SHORTCODE_NAME = 'growtype_form';

    const GROWTYPE_FORM_SUBMITTED_INPUT = 'growtype_form_submitted';
    const GROWTYPE_FORM_SUBMITTER_ID = 'form_submitter_id';
    const GROWTYPE_FORM_NAME_IDENTIFICATOR = 'growtype_form_name';

    const GROWTYPE_FORM_ALLOWED_FIELD_TYPES = ['input', 'textarea', 'file', 'email', 'select', 'checkbox', 'hidden', 'number', 'password'];
    const EXCLUDED_VALUES_FROM_VALIDATION = [self::GROWTYPE_FORM_SUBMITTED_INPUT, self::GROWTYPE_FORM_SUBMITTER_ID, self::GROWTYPE_FORM_NAME_IDENTIFICATOR];

    public function __construct()
    {
        if (!is_admin()) {
            add_filter('wp_loaded', array ($this, 'growtype_form_process_posted_data'));
            add_shortcode(self::GROWTYPE_FORM_SHORTCODE_NAME, array ($this, 'growtype_form_shortcode_function'));
            add_filter('body_class', array ($this, 'growtype_form_body_class'));
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
        if (isset($_POST[self::GROWTYPE_FORM_SUBMITTED_INPUT]) && sanitize_text_field($_POST[self::GROWTYPE_FORM_SUBMITTED_INPUT]) === 'true') {

            $form_name = sanitize_text_field($_POST[self::GROWTYPE_FORM_NAME_IDENTIFICATOR]);

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
     * Add login class to body
     */
    function growtype_form_body_class($classes)
    {
        global $post;

        if (!empty($post) && has_shortcode($post->post_content, self::GROWTYPE_FORM_SHORTCODE_NAME)) {
            $classes[] = 'growtype-form';
        }

        if (growtype_form_login_page_is_active()) {
            $classes[] = 'login-' . growtype_form_get_login_page_template();
        }

        if (growtype_form_signup_page_is_active()) {
            $classes[] = 'signup-' . growtype_form_get_signup_page_template();
        }

        return $classes;
    }

    /**
     * Required scripts
     */
    function growtype_form_enqueue_general_styles()
    {
        wp_enqueue_style('growtype-form.css', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/styles/growtype-form.css', array (), '1.0', 'all');
    }

    /**
     * Upload form shortcode
     * [growtype_form name="{form_name}"]
     */
    function growtype_form_shortcode_function($args)
    {
        global $wp_session;

        /**
         * If empty shortcode arguments, return empty
         */
        if (empty($args)) {
            return null;
        }

        /**
         * Form name
         */
        $form_name = $args['name'];

        /**
         * Get form data
         */
        $form_data = $this->get_growtype_form_data($form_name);

        if (empty($form_data)) {
            return __('Form is not configured. Please contact site admin.', 'growtype-form');
        }

        /**
         * Enqueue general scripts
         */
        $this->growtype_form_enqueue_general_styles();

        /**
         * Render forms
         */
        if (str_contains($form_name, 'login')) {
            /**
             * Render login form
             */
            $growtype_login = new Growtype_Login_Render();

            return $growtype_login->render_growtype_login_form($form_data);
        } else {
            /**
             * Enqueue scripts
             */
            $this->growtype_form_enqueue_validation_scripts();

            /**
             * Initiate scripts
             */
            add_action('wp_footer', function () {
                $this->growtype_form_url_scripts_init();
                $this->growtype_form_validation_scripts_init();
            }, 99);

            /**
             * Render form
             */
            return $this->render_growtype_general_form($form_data, $form_name);
        }
    }

    /**
     * @param $form_name
     * @return mixed|null
     */
    function get_growtype_form_data($form_name)
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

        return $available_forms[$form_name] ?? null;
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
        $skipped_values = ['username', 'password', 'repeat_password', 'email', 'submit', 'growtype_form_submitted'];

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

        $response['user_id'] = $user_id;
        $response['success'] = true;

        return $response;
    }

    /**
     * @param $fields
     * @param $posted_data
     * @return array
     * Map post fields with shortcode fields
     */
    function form_submitted_values_are_valid($form_data, $submitted_values)
    {
        $available_fields = $form_data['main_fields'] ?? null;

        if (empty($available_fields)) {
            return false;
        }

        if (isset($form_data['confirmation_fields']) && !empty($form_data['confirmation_fields'])) {
            $available_fields = array_merge($form_data['main_fields'], $form_data['confirmation_fields']);
        }

        $available_fields_names = [];
        foreach ($available_fields as $field) {
            $field_name = strtok($field['name'], '[');
            array_push($available_fields_names, $field_name);
        }

        $submitted_data = array_merge($submitted_values['data'], $submitted_values['files']);

        foreach ($submitted_data as $key => $value) {
            if (in_array($key, self::EXCLUDED_VALUES_FROM_VALIDATION)) {
                continue;
            }
            if (!in_array($key, $available_fields_names)) {
                return false;
            }
        }

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
         * Get form data
         */
        $form_data = $this->get_growtype_form_data($form_name);

        if (empty($form_data)) {
            return null;
        }

        /**
         * Validate if submitted values match available values
         */
        $submitted_data_are_valid = $this->form_submitted_values_are_valid($form_data, $submitted_values);

        $submitted_data = $submitted_values['data'];

        if ($submitted_data_are_valid) {

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

                        return growtype_form_redirect_url_after_signup();
                    }
                }
            } elseif ($form_name === 'wc_product') {

                require_once plugin_dir_path(dirname(__FILE__)) . 'crud/wc.php';

                $product_data = $submitted_values;

                /**
                 * Format long description if contains multiple descriptions
                 */
                if (isset($product_data['data']['long_description']) && is_array($product_data['data']['long_description'])) {

                    $long_description_fields = [];
                    foreach ($form_data['main_fields'] as $field) {
                        if (str_contains($field['name'], 'long_description')) {
                            $field_key = str_replace('long_description[', '', $field['name']);
                            $field_key = str_replace(']', '', $field_key);
                            $long_description_fields[$field_key] = $field;
                        }
                    }

                    $long_description_formatted = '';
                    foreach ($product_data['data']['long_description'] as $key => $description) {
                        $label = $long_description_fields[$key]['label'];
                        $long_description_formatted .= '<b>' . $label . '</b>' . "\n" . $description . "\n" . "\n";
                    }

                    $product_data['data']['long_description'] = $long_description_formatted;
                }

                $wc_crud = new Growtype_Form_Wc_Crud();
                $submit_data = $wc_crud->create_product($product_data);

                if ($submit_data['success']) {
                    $product_id = $submit_data['product_id'];

                    $submit_data['product_id'] = $product_id;
                    $submit_data['success'] = true;
                    $submit_data['message'] = $success_message ?? __('Product uploaded.', 'growtype-form');
                }
            } elseif ($form_name === 'post') {

                require_once plugin_dir_path(dirname(__FILE__)) . 'crud/wp.php';

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
                $submit_data['message'] = __('Missing data. Please contact site admin.', 'growtype-form');
            }
        } else {
            $fields_values_args = $submitted_data;

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

        $query_args = array_merge($status_args, $fields_values_args ?? []);

        return add_query_arg($query_args, get_permalink());
    }
}
