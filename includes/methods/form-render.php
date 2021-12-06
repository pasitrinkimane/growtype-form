<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Render
{
    const GROWTYPE_FORM_SUBMITTED_INPUT = 'growtype_form_submitted';
    const GROWTYPE_FORM_SUBMITTER_ID = 'form_submitter_id';
    const GROWTYPE_FORM_ALLOWED_FIELD_TYPES = ['input', 'textarea', 'file', 'email', 'select', 'checkbox', 'hidden', 'number'];
    const EXCLUDED_VALUES_FROM_VALIDATION = [self::GROWTYPE_FORM_SUBMITTED_INPUT, self::GROWTYPE_FORM_SUBMITTER_ID];

    public function __construct()
    {
        if (!is_admin()) {
            add_shortcode('growtype_form', array ($this, 'growtype_form_shortcode_function'));
        }
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
         * Process posted values and redirect
         */
        if (isset($_POST[self::GROWTYPE_FORM_SUBMITTED_INPUT]) && sanitize_text_field($_POST[self::GROWTYPE_FORM_SUBMITTED_INPUT]) === 'true') {
            $submitted_values = [
                'files' => $_FILES,
                'data' => $_POST,
            ];

            $response = $this->process_form_submitted_values($form_name, $submitted_values);
            wp_redirect($response);
            exit();
        }

        /**
         * Get form data
         */
        $form = $this->get_growtype_form_data($form_name);

        if (empty($form)) {
            return __('Form is not configured. Please contact site admin.', 'growtype-form');
        }

        /**
         * Enqueue scripts
         */
        $this->Growtype_Form_upload_data_enqueue_scripts();

        /**
         * Initiate scripts
         */
        add_action('wp_footer', function () {
            $this->Growtype_Form_upload_scripts_init();
            $this->Growtype_Form_upload_validation_scripts_init();
        }, 99);

        /**
         * Render form
         */
        return $this->render_growtype_form($form);
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
        if ($form_name === 'wc_product_upload_form') {
            $form_json_content = get_option('wc_product_upload_form_json_content');
        } elseif ($form_name === 'post_upload_form') {
            $form_json_content = get_option('upload_post_form_json_content');
        } else {
            $form_json_content = get_option('growtype_form_json_content');
        }

        if (empty($form_json_content)) {
            return null;
        }

        $available_forms = json_decode($form_json_content, true);

        return $available_forms[$form_name] ?? null;
    }

    /**
     * @param $form
     * @return false|string|null
     */
    function render_growtype_form($form)
    {
        /**
         * Form main fields
         */
        $main_fields = $form['main_fields'] ?? null;

        if (empty($main_fields)) {
            return null;
        }

        /**
         * Recaptcha setup
         */
        $recaptcha = $form['recaptcha'] ?? null;
        $recaptcha_key = $recaptcha['api_key'] ?? null;

        if (!empty($recaptcha_key) && !function_exists('recaptcha_setup')) {
            add_action('wp_footer', function () use (&$recaptcha_key) {
                $this->recaptcha_setup($recaptcha_key);
            }, 99);
        }

        /**
         * Form confirmation fields
         */
        $confirmation_fields = $form['confirmation_fields'] ?? null;

        /**
         * Form title
         */
        $form_title = $form['title'] ?? null;

        /**
         * Form title
         */
        $form_type = $form['type'] ?? null;

        ob_start();
        ?>
        <div class="growtype-form-wrapper">

            <?php $this->render_growtype_form_response_status(); ?>

            <div id="growtype-form-container" class="container">
                <div class="form-wrapper">
                    <?php if (!empty($form_title)) { ?>
                        <h2 class="e-title-intro"><?= $form_title ?></h2>
                    <?php } ?>
                    <form id="growtype-form" <?= $form_type === 'upload' ? 'enctype="multipart/form-data"' : '' ?> class="form" action="<?php the_permalink(); ?>" method="post">
                        <div class="row g-3 fields-main">
                            <?php
                            foreach ($main_fields as $field) {
                                $this->render_growtype_form_field($field);
                            }
                            ?>
                        </div>

                        <?php
                        if (!empty($confirmation_fields)) { ?>
                            <div class="row fields-confirmation">
                                <?php
                                foreach ($confirmation_fields as $field) {
                                    $this->render_growtype_form_field($field);
                                }
                                ?>
                            </div>
                        <?php } ?>

                        <div class="row row-submit">
                            <div class="col-12">

                                <input type="text" hidden name='<?= self::GROWTYPE_FORM_SUBMITTED_INPUT ?>' value="true"/>
                                <input type="text" hidden name='<?= self::GROWTYPE_FORM_SUBMITTER_ID ?>' value="<?= get_current_user_id() ?? null ?>"/>

                                <?php if (!empty($recaptchav3)) { ?>
                                    <div class="g-recaptcha"
                                         data-sitekey="<?= $recaptchav3 ?>"
                                         data-size="invisible"
                                         data-callback="uploadFormSubmit">
                                    </div>
                                <?php } ?>

                                <button type="submit" class="btn btn-primary"><?= $form['submit_label'] ?? __("Save", "growtype-form") ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php

        return ob_get_clean();
    }

    /**
     *
     */
    function render_growtype_form_field($field)
    {
        $field_name = $field['name'] ?? false;
        $field_required = isset($field['required']) && $field['required'] === 'true' ? true : false;
        $field_type = $field['type'];
        $field_hidden = $field['hidden'] ?? false;

        if ($field_type === 'hidden') {
            $field_hidden = true;
        }

        $field_value = $field['value'] ?? sanitize_text_field(filter_input(INPUT_GET, $field_name));

        $field_options = $field['options'] ?? null;
        $field_label = $field['label'] ?? null;
        $field_label = $field_required && !str_contains($field_label, '*') ? $field_label . '*' : $field_label;
        $field_description = $field['description'] ?? null;
        $placeholder = $field['placeholder'] ?? null;
        $field_accept = $field['accept'] ?? null;
        $field_min_value = $field['min'] ?? null;
        $field_max_value = $field['max'] ?? null;

        $field_col_class = $field['col_class'] ?? 'col-auto';

        if (!in_array($field_type, self::GROWTYPE_FORM_ALLOWED_FIELD_TYPES)) {
            return null;
        }

        ?>

        <div class="<?= $field_col_class ?>" style="<?= $field_hidden ? 'display:none;' : '' ?>" data-name="<?= $field_name ?>">
            <?php
            if ($field_type === 'select') { ?>
                <?php
                if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php }
                ?>
                <select name="<?= $field_name ?>" id="<?= $field_name ?>">
                    <?php
                    foreach ($field_options as $field_option) { ?>
                        <option value="<?= sanitize_text_field($field_option['value']) ?>"><?= sanitize_text_field($field_option['label']) ?></option>
                    <?php } ?>
                </select>
            <?php } elseif ($field_type === 'radio') { ?>
                <?php
                foreach ($field_options as $field_option) { ?>
                    <div class="radio-wrapper">
                        <input type="radio" id="<?= str_replace(' ', '_', strtolower($field_option)) ?>" name="<?= $field_name ?>" value="<?= strtolower($field_option) ?>" <?= $field_required ? 'required' : '' ?>>
                        <label for="<?= str_replace(' ', '_', strtolower($field_option)) ?>"><?= str_replace('_', ' ', $field_option) ?></label>
                    </div>
                <?php } ?>
            <?php } elseif ($field_type === 'checkbox') { ?>
                <div class="form-check">
                    <input type="<?= $field_type ?>"
                           class="form-check-input"
                           name="<?= $field_name ?>"
                           id="<?= $field_name ?>"
                           placeholder="<?= $placeholder ?>"
                        <?= $field_required ? 'required' : '' ?>
                           value="<?= !str_contains($field_name, 'password') ? $field_value : null ?>"
                    >
                    <?php
                    if (!empty($field_label)) { ?>
                        <label for="<?= $field_name ?>" class="form-label">
                            <?= $field_label ?>
                        </label>
                    <?php }
                    ?>
                </div>
            <?php } elseif ($field_type === 'textarea') { ?>
                <?php
                if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php }
                ?>
                <?php if (!empty($field_description)) { ?>
                    <p class="form-description"><?= $field_description ?></p>
                <?php } ?>
                <textarea id="<?= $field_name ?>" name="<?= $field_name ?>" rows="4" cols="50" placeholder="<?= $placeholder ?>" <?= $field_required ? 'required' : '' ?>></textarea>
            <?php } elseif ($field_type === 'file') { ?>
                <?php if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php } ?>
                <input type="<?= $field_type ?>" id="<?= $field_name ?>" name="<?= $field_name ?>" accept="<?= $field_accept ?>" <?= $field_required ? 'required' : '' ?>>
            <?php } else { ?>
                <?php
                if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php }
                ?>
                <?php if (!empty($field_description)) { ?>
                    <p class="form-description"><?= $field_description ?></p>
                <?php } ?>
                <input type="<?= $field_type ?>"
                       class="form-control"
                       name="<?= $field_name ?>"
                       id="<?= $field_name ?>"
                       placeholder="<?= $placeholder ?? null ?>"
                    <?= $field_required ? 'required' : '' ?>
                       value="<?= !str_contains($field_name, 'password') ? $field_value : null ?>"
                    <?= $field_min_value ? 'min="' . $field_min_value . '"' : '' ?>
                    <?= $field_max_value ? 'max="' . $field_max_value . '"' : '' ?>
                >
            <?php } ?>
        </div>

        <?php
    }

    /**
     *
     */
    function render_growtype_form_response_status()
    {
        $response_status = $_GET['status'] ?? null;

        if (!empty($response_status)) {

            $response_status_message = sanitize_text_field(filter_input(INPUT_GET, 'message'));

            if ($response_status === 'success') { ?>
                <div class="alert alert-success" role="alert">
                    <?= __($response_status_message, "growtype-form") ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger" role="alert">
                    <?= __($response_status_message, "growtype-form") ?>
                </div>
            <?php }
        }
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
        $skipped_values = ['username', 'password', 'repeat_password', 'email', 'submit'];

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
     * @param $data
     * @return array
     */
    function save_submitted_registration_data($data)
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

        $validate_password = validate_password($password);

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

        if (is_wp_error($user_id)) {
            $response['success'] = false;
            $response['message'] = __("Profile already registered.", "growtype-form");
        } else {
            $response = $this->update_user_data($user_id);

            if ($response['success']) {
                $response['message'] = __("Registration successful.", "growtype-form");
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
    function form_submitted_values_are_valid($form_data, $submitted_values)
    {
        $available_fields = array_merge($form_data['main_fields'], $form_data['confirmation_fields']);

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
     * Required scripts
     */
    function Growtype_Form_upload_data_enqueue_scripts()
    {
        wp_enqueue_style('growtype-form-upload-css', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/styles/growtype-form.css', array (), '1.0', 'all');
        wp_enqueue_script('jquery.validate.js', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js', '', '', true);

        if (get_locale() === 'lt_LT') {
            wp_enqueue_script('jquery.validate.js.localization', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/localization/messages_lt.js', '', '', true);
        }
    }

    /**
     *
     */
    function Growtype_Form_upload_scripts_init()
    {
        ?>
        <script>
            if (window.location.search.length > 0 && window.location.search.indexOf('action') !== -1) {
                window.history.replaceState(null, null, window.location.pathname);
            } else if (window.location.search.length > 0 && window.location.search.indexOf('message') !== -1) {
                window.growtypeWcUploadFormFailed = true;
                window.history.replaceState(null, null, window.location.pathname);
            }
        </script>
        <?php
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

            if ($form_name === 'registration_form') {
                $submit_data = $this->save_submitted_registration_data($submitted_data);

                if ($submit_data['success']) {
                    $user_id = $submit_data['user_id'];

                    wp_set_current_user($user_id, $user_id);
                    wp_set_auth_cookie($user_id);
                    do_action('wp_login', $user_id);

                    return get_permalink(get_page_by_path('profile')->ID);
                }
            } elseif ($form_name === 'wc_product_upload_form') {

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
            } elseif ($form_name === 'post_upload_form') {

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

    /**
     * Validate form
     */
    function Growtype_Form_upload_validation_scripts_init()
    {
        ?>
        <script>
            $.validator.setDefaults({ignore: ":hidden:not(select)"});

            if ($("#growtype-form select").length > 0) {
                $("#growtype-form select").each(function () {
                    if ($(this).attr('required') !== undefined) {
                        $(this).on("change", function () {
                            $(this).valid();
                        });
                    }
                });
            }

            $('#growtype-form button[type="submit"]').click(function () {
                var isValid = $("#growtype-form").valid();
                if (!isValid) {
                    event.preventDefault();
                }
            });

            $('#growtype-form').validate({
                errorPlacement: function (error, element) {
                    // console.log(element)
                    if (element.is("#growtype-form select")) {
                        element.closest(".col-auto").append(error);
                    } else if (element.is("#growtype-form input[type='checkbox']")) {
                        element.closest(".form-check").append(error);
                    } else {
                        error.insertAfter(element);
                    }
                },
                // messages: {
                //     occupation: {
                //         required: "Pasirinkite kategoriją",
                //     },
                //     first_and_last_name: {
                //         required: "Šis laukas būtinas",
                //     },
                //     email: {
                //         required: "Šis laukas būtinas",
                //     },
                //     phone: {
                //         required: "Šis laukas būtinas",
                //     },
                //     birthday: {
                //         required: "Šis laukas būtinas",
                //     },
                //     country: {
                //         required: "Šis laukas būtinas",
                //     },
                //     password: {
                //         required: "Šis laukas būtinas",
                //     },
                //     repeat_password: {
                //         required: "Šis laukas būtinas",
                //     },
                //     terms_checkbox: {
                //         required: "Šis laukas būtinas",
                //     },
                //     child_first_and_last_name: {
                //         required: "Šis laukas būtinas",
                //     },
                //     username: {
                //         required: "Šis laukas būtinas",
                //     },
                //     confirm_checkbox: {
                //         required: "Šis laukas būtinas",
                //     },
                // },
            });

            $('#growtype-form').validate().settings.ignore = ".chosen-search-input";
        </script>
        <?php
    }

    /**
     * @param $recaptchav3
     */
    function recaptcha_setup($recaptcha_key)
    {
        ?>
        <style>
            .grecaptcha-badge {
                display: none !important;
            }
        </style>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptcha_key ?>"></script>
        <script>
            $('#growtype-form').submit(function (event) {
                event.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                grecaptcha.reset();
                grecaptcha.execute();
            });

            function uploadFormSubmit(token) {
                document.getElementById("growtype-form").submit();
            }
        </script>
        <?php
    }
}
