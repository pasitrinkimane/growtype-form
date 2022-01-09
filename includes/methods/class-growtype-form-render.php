<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Render
{
    const GROWTYPE_FORM_SHORTCODE_NAME = 'growtype_form';

    const GROWTYPE_FORM_SUBMITTED_INPUT = 'growtype_form_submitted';
    const GROWTYPE_FORM_SUBMITTER_ID = 'form_submitter_id';

    const GROWTYPE_FORM_NAME_IDENTIFICATOR = 'growtype_form_name';
    const GROWTYPE_FORM_POST_IDENTIFICATOR = 'growtype_form_post_id';

    const GROWTYPE_FORM_ALLOWED_FIELD_TYPES = ['input', 'textarea', 'file', 'email', 'select', 'checkbox', 'hidden', 'number', 'password', 'custom'];

    const ALTERNATIVE_SUBMITTED_DATA_KEYS = [
        'name' => 'name_s'
    ];

    protected $Growtype_Form_Login;
    protected $Growtype_Form_Crud;

    public function __construct()
    {
        if (!is_admin()) {
            add_shortcode(self::GROWTYPE_FORM_SHORTCODE_NAME, array ($this, 'growtype_form_shortcode_function'));
            add_filter('body_class', array ($this, 'growtype_form_body_class'));

            $this->load_methods();
        }
    }

    /**
     * @return void
     */
    function load_methods()
    {
        require_once Growtype_Form_Path . 'includes/methods/login/class-growtype-form-login.php';
        $this->Growtype_Form_Login = new Growtype_Form_Login();

        require_once Growtype_Form_Path . 'includes/methods/crud/class-growtype-form-crud.php';
        $this->Growtype_Form_Crud = new Growtype_Form_Crud();
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
        $form_data = Growtype_Form_Crud::get_growtype_form_data($form_name);

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
            return Growtype_Form_Login::render_growtype_login_form($form_data);
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
     * @param $form
     * @return false|string|null
     */
    function render_growtype_general_form($form, $form_name)
    {
        /**
         * Form main fields
         */
        $main_fields = $form['main_fields'] ?? null;

        if (empty($main_fields)) {
            return null;
        }

        /**
         * Check if product form add fill input with existing values
         */
        if (class_exists('woocommerce') && isset($_REQUEST['customize']) && $_REQUEST['customize'] === 'edit' && str_contains($form_name, 'wc_product')) {
            $product = wc_get_product(get_the_ID());

            $_REQUEST['title'] = $product->get_title();
            $_REQUEST['description'] = $product->get_description();
            $_REQUEST['short_description'] = $product->get_short_description();
            $_REQUEST['featured_image'] = wp_get_attachment_url($product->get_image_id());
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

        /**
         * Login btn
         */
        $login_btn = $form['login_btn'] ?? null;

        /**
         * Post data
         */
        $post = get_post();

        ob_start();
        ?>

        <div class="growtype-form-wrapper">

            <?php $this->render_growtype_form_response_status(); ?>

            <div class="growtype-form-container">
                <div class="form-wrapper">
                    <?php if (!empty($form_title)) { ?>
                        <h2 class="e-title-intro"><?= $form_title ?></h2>
                    <?php } ?>

                    <form id="growtype-form" enctype="multipart/form-data" class="form" action="<?php the_permalink(); ?>" method="post" data-name="<?= $form_name ?>">
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
                                <input type="text" hidden name='<?= self::GROWTYPE_FORM_NAME_IDENTIFICATOR ?>' value="<?= $form_name ?>"/>

                                <?php if (!empty($post)) { ?>
                                    <input type="text" hidden name='<?= self::GROWTYPE_FORM_POST_IDENTIFICATOR ?>' value="<?= $post->ID ?>"/>
                                <?php } ?>

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

                    <?php
                    if ($login_btn) { ?>
                        <div class="b-actions">
                            <label for=""><?= __("Do you already have an account?", "growtype-registration") ?></label>
                            <a class="btn btn-link" href="<?= growtype_form_login_url() ?>"><?= __("Sign in", "growtype-registration") ?></a>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>

        <?php

        return ob_get_clean();
    }

    /**
     * @param $field
     * @return null
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

        $field_value = isset($field['value']) ? sanitize_text_field($field['value']) : null;

        if (empty($field_value)) {
            $field_value = isset($_REQUEST[$field_name]) ? sanitize_text_field($_REQUEST[$field_name]) : null;
        }

        if ($field_name === 'name') {
            $field_value = $_REQUEST[self::ALTERNATIVE_SUBMITTED_DATA_KEYS[$field_name]] ?? null;
        }

        if (str_contains($field_name, 'password')) {
            $field_value = null;
        }

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
            /**
             * Select
             */
            if ($field_type === 'select') {
            if (!empty($field_label)) { ?>
                <label for="<?= $field_name ?>" class="form-label">
                    <?= $field_label ?>
                </label>
            <?php } ?>
                <select name="<?= $field_name ?>" id="<?= $field_name ?>" <?= $field_required ? 'required' : '' ?>>
                    <?php
                    /**
                     * Use woocommerce country select
                     */
                    if (class_exists('woocommerce') && $field_value === 'wc_country' && $field_type === 'select') {
                        $field_options = array ('' => __('Select a country / region&hellip;', 'woocommerce')) + WC()->countries->get_allowed_countries();

                        foreach ($field_options as $key => $field_option) { ?>
                            <option value="<?= $key ?>"><?= $field_option ?></option>
                        <?php }
                    } else {
                        foreach ($field_options as $field_option) { ?>
                            <option value="<?= sanitize_text_field($field_option['value']) ?>"><?= sanitize_text_field($field_option['label']) ?></option>
                        <?php }
                    } ?>
                </select>
            <?php
            /**
             * Radio
             */
            } elseif ($field_type === 'radio') {
            foreach ($field_options

            as $field_option) { ?>
                <div class="radio-wrapper">
                    <input type="radio" id="<?= str_replace(' ', '_', strtolower($field_option)) ?>" name="<?= $field_name ?>" value="<?= strtolower($field_option) ?>" <?= $field_required ? 'required' : '' ?>>
                    <label for="<?= str_replace(' ', '_', strtolower($field_option)) ?>"><?= str_replace('_', ' ', $field_option) ?></label>
                </div>
            <?php }
            /**
             * Checkbox
             */
            } elseif ($field_type === 'checkbox') { ?>
                <div class="form-check">
                    <input type="<?= $field_type ?>"
                           class="form-check-input"
                           name="<?= $field_name ?>"
                           id="<?= $field_name ?>"
                           placeholder="<?= $placeholder ?>"
                        <?= $field_required ? 'required' : '' ?>
                           value="<?= $field_value ?>"
                    >
                    <?php
                    if (!empty($field_label)) { ?>
                        <label for="<?= $field_name ?>" class="form-label">
                            <?= $field_label ?>
                        </label>
                    <?php }
                    ?>
                </div>
            <?php
            /**
             * Textarea
             */
            } elseif ($field_type === 'textarea') { ?>
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
                <textarea id="<?= $field_name ?>" name="<?= $field_name ?>" rows="4" cols="50" placeholder="<?= $placeholder ?>" <?= $field_required ? 'required' : '' ?>><?= $field_value ?></textarea>
            <?php
            /**
             * File
             */
            } elseif ($field_type === 'file') { ?>
            <?php if (!empty($field_label)) { ?>
                <label for="<?= $field_name ?>" class="form-label">
                    <?= $field_label ?>
                </label>
            <?php } ?>
                <div class="img-wrapper">
                    <?php
                    if (!empty($field_value)) { ?>
                        <img class="img-fluid" src="<?= $field_value ?>" alt="" style="max-width: 150px;">
                        <span class="btn-img-remove dashicons dashicons-remove" data-type="<?= $field_type ?>" data-id="<?= $field_name ?>" data-name="<?= $field_name ?>" data-accept="<?= $field_accept ?>" data-required="<?= $field_required ?>" style="cursor: pointer;"></span>
                    <?php } else { ?>
                        <input type="<?= $field_type ?>" id="<?= $field_name ?>" name="<?= $field_name ?>" accept="<?= $field_accept ?>" <?= $field_required ? 'required' : '' ?>>
                    <?php } ?>
                </div>
                <script>
                    $('.btn-img-remove').click(function () {
                        let type = $(this).attr('data-type');
                        let id = $(this).attr('data-id');
                        let name = $(this).attr('data-name');
                        let accept = $(this).attr('data-accept');
                        let required = $(this).attr('data-required');
                        $(this).closest('.img-wrapper').hide();
                        $(this).closest('.col-auto').append('<input type="' + type + '" id="' + id + '" name="' + name + '"  accept="' + accept + '"  ' + required + '>');
                    });
                </script>
            <?php
            /**
             * Custom, skip sanitization
             */
            } elseif ($field_type === 'custom') {
                echo $field['value'];
                /**
                 * Input
                 */
            }
            else { ?>
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
                   class="form-control <?= !empty($field_value) ? 'has-value' : '' ?>"
                   name="<?= $field_name ?>"
                   id="<?= $field_name ?>"
                   placeholder="<?= $placeholder ?? null ?>"
                <?= $field_required ? 'required' : '' ?>
                   value="<?= $field_value ?>"
                <?= $field_min_value ? 'min="' . $field_min_value . '"' : '' ?>
                <?= $field_max_value ? 'max="' . $field_max_value . '"' : '' ?>
            >
            <?php } ?>
        </div>

        <?php
    }

    /**
     * Response status
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
     * Required scripts
     */
    function growtype_form_enqueue_validation_scripts()
    {
        wp_enqueue_script('jquery.validate.js', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js', '', '', true);

        if (get_locale() === 'lt_LT') {
            wp_enqueue_script('jquery.validate.js.localization', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/localization/messages_lt.js', '', '', true);
        }
    }

    /**
     *
     */
    function growtype_form_url_scripts_init()
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
     * Validate form
     */
    function growtype_form_validation_scripts_init()
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
                }
            });

            $('#growtype-form').validate().settings.ignore = ".chosen-search-input";
        </script>
        <?php
    }

    /**
     * @param $recaptcha_key
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
