<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Render
{
    use Notice;
    use File;
    use Product;

    const GROWTYPE_FORM_SHORTCODE_NAME = 'growtype_form';

    const GROWTYPE_FORM_ALLOWED_FIELD_TYPES = ['text', 'textarea', 'file', 'email', 'select', 'radio', 'checkbox', 'hidden', 'number', 'password', 'custom', 'repeater', 'shortcode'];

    const DATE_TIME_DATA = [
        'date_format' => "yy-mm-dd"
    ];

    protected $Growtype_Form_Login;
    protected $Growtype_Form_Crud;

    public function __construct()
    {
        if (!is_admin()) {
            add_shortcode(self::GROWTYPE_FORM_SHORTCODE_NAME, array ($this, 'growtype_form_shortcode_function'));
        }
    }

    /**
     * Add login class to body
     */
    function growtype_form_page_body_class($classes)
    {
        $classes[] = 'has-growtype-form';

        if (growtype_form_login_page_is_active()) {
            if (empty(get_option('growtype_form_login_show_footer')) || !get_option('growtype_form_login_show_footer')) {
                $classes[] = 'footer-disabled';
            }
        }

        if (growtype_form_signup_page_is_active()) {
            if (empty(get_option('growtype_form_signup_show_footer')) || !get_option('growtype_form_signup_show_footer')) {
                $classes[] = 'footer-disabled';
            }
        }

        return $classes;
    }

    /**
     * Add signup class to body
     */
    function growtype_form_signup_body_class($classes)
    {
        $classes[] = 'signup-' . growtype_form_get_signup_page_template();

        return $classes;
    }

    /**
     * Add login class to body
     */
    function growtype_form_login_body_class($classes)
    {
        $classes[] = 'login-' . growtype_form_get_login_page_template();

        return $classes;
    }

    /**
     * Required scripts
     */
    function growtype_form_enqueue_render_styles($form_name)
    {
        /**
         * Main css
         */
        wp_enqueue_style('growtype-form-render', GROWTYPE_FORM_URL_PUBLIC . 'styles/growtype-form-render.css', array (), '1.1', 'all');

        /**
         * Styles
         */
        if (!wp_script_is('image-uploader', 'enqueued')) {
            wp_enqueue_style('image-uploader', GROWTYPE_FORM_URL_PUBLIC . 'plugins/image-uploader/image-uploader.css', array (), '1.1', 'all');
        }

        /**
         * Jquery UI
         */
        if (!wp_script_is('jquery-ui', 'enqueued')) {
            wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css', array (), '1.1', 'all');
        }

        /**
         * Timepicker
         */
        if (!wp_script_is('timepicker-addon', 'enqueued')) {
            wp_enqueue_style('timepicker-addon', GROWTYPE_FORM_URL_PUBLIC . 'plugins/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css', array (), '1.1', 'all');
        }

        /**
         * Signup styles
         */
        if (str_contains($form_name, 'signup')) {
            wp_enqueue_style('growtype-form-signup', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/signup/main.css', array (), '1.1', 'all');
        }

        /**
         * Signup styles
         */
        if (str_contains($form_name, 'login')) {
            wp_enqueue_style('growtype-form-login', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/login/main.css', array (), '1.1', 'all');
        }
    }

    /**
     * Required scripts
     */
    function growtype_form_enqueue_render_scripts()
    {
        wp_enqueue_script('growtype-form-render', GROWTYPE_FORM_URL_PUBLIC . 'scripts/growtype-form-render.js', array ('jquery'), '1.3', true);

        wp_localize_script('growtype-form-render', 'growtype_form_date_time_data', self::DATE_TIME_DATA);

        /**
         * File styles
         */
        if (!wp_script_is('bootstrap-filestyle', 'enqueued')) {
            wp_enqueue_script('bootstrap-filestyle', GROWTYPE_FORM_URL_PUBLIC . 'plugins/bootstrap-filestyle/src/bootstrap-filestyle.min.js', array ('jquery'), '1.1', true);
        }

        /**
         * Image uploader
         */
        if (!wp_script_is('image-uploader', 'enqueued')) {
            wp_enqueue_script('image-uploader', GROWTYPE_FORM_URL_PUBLIC . 'plugins/image-uploader/image-uploader.js', array ('jquery'), '1.1', true);
        }

        /**
         * Jquery multifile
         */
        if (!wp_script_is('jquery-multifile', 'enqueued')) {
            wp_enqueue_script('image-multifile', GROWTYPE_FORM_URL_PUBLIC . 'plugins/jquery-multifile/jquery.MultiFile.min.js', array ('jquery'), '1.1', true);
        }

        /**
         * Jquery UI
         */
        if (!wp_script_is('jquery-ui', 'enqueued') && !wp_script_is('jquery-ui-core', 'enqueued')) {
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.1/jquery-ui.js', array ('jquery'), '1.1', true);
        }

        if (!wp_script_is('timepicker-addon', 'enqueued')) {
            wp_enqueue_script('timepicker-addon', GROWTYPE_FORM_URL_PUBLIC . 'plugins/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js', array ('jquery'), '1.1', true);
        }

        /**
         * Autonumeric
         */
        if (!wp_script_is('autoNumeric', 'enqueued')) {

            wp_enqueue_script('autoNumeric', GROWTYPE_FORM_URL_PUBLIC . 'plugins/autoNumeric/autoNumeric.min.js', array ('jquery'), '1.1', true);

            $currency_pos = 'left';
            $currency_symbol = '&euro;';
            $digitGroupSeparator = ',';
            $decimalCharacter = '.';
            $decimalPlacesOverride = 2;

            if (class_exists('woocommerce')) {
                $currency_pos = get_option('woocommerce_currency_pos');
                $currency_symbol = get_woocommerce_currency_symbol();
                $digitGroupSeparator = wc_get_price_thousand_separator();
                $decimalCharacter = wc_get_price_decimal_separator();
                $decimalPlacesOverride = wc_get_price_decimals();
            }

            switch ($currency_pos) {
                case 'left':
                    $currency_symbol_placement = 'p';
                    break;
                case 'right':
                    $currency_symbol_placement = 's';
                    break;
                case 'left_space':
                    $currency_symbol_placement = 'p';
                    $currency_symbol = $currency_symbol . ' ';
                    break;
                case 'right_space':
                    $currency_symbol_placement = 's';
                    $currency_symbol = ' ' . $currency_symbol;
                    break;
            }

            $currency_data = array (
                'currencySymbolPlacement' => $currency_symbol_placement,
                'digitGroupSeparator' => $digitGroupSeparator,
                'decimalCharacter' => $decimalCharacter,
                'currencySymbol' => $currency_symbol,
                'decimalPlacesOverride' => $decimalPlacesOverride,
            );

            wp_localize_script('autoNumeric', 'autoNumericdata', $currency_data);
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
         * Init page body classes
         */
        add_filter('body_class', array ($this, 'growtype_form_page_body_class'));

        /**
         * Form name
         */
        $form_name = $args['name'];
        $form_type = $args['type'] ?? null;

        /**
         * Get form data
         */
        $form_data = Growtype_Form_Crud::get_growtype_form_data($form_name);

        /**
         * Overwrite form data with existing extra arguments
         */
        $extra_args = $args;

        unset($extra_args["name"]);

        if (!empty($extra_args)) {
            foreach ($form_data as $key => $form_data_element) {
                if (in_array($key, array_keys($extra_args))) {
                    $form_data[$key] = $extra_args[$key] == 'true' || $extra_args[$key] == 'false' ? filter_var($extra_args[$key], FILTER_VALIDATE_BOOLEAN) : $extra_args[$key];
                }
            }
        }

        if (empty($form_data)) {
            return __('Form is not configured. Please contact site admin.', 'growtype-form');
        }

        /**
         * Init form body classes
         */
        if (str_contains($form_name, 'signup')) {
            add_filter('body_class', array ($this, 'growtype_form_signup_body_class'));
        } elseif (str_contains($form_name, 'login')) {
            add_filter('body_class', array ($this, 'growtype_form_login_body_class'));
        }

        /**
         * Enqueue render scripts
         */
        $this->growtype_form_enqueue_render_styles($form_name);
        $this->growtype_form_enqueue_render_scripts();

        /**
         * Check if edit page and update post request data
         */
        $this->update_return_data(get_the_ID(), $form_data, $form_name);

        /**
         * Render forms
         * $form_type Fields - plain fields with no extra features
         */
        if (str_contains($form_name, 'login')) {
            /**
             * Render login form
             */
            return Growtype_Form_Login::render_growtype_login_form($form_data);
        } elseif ($form_type === 'fields') {
            return $this->render_growtype_fields($form_data, $form_name);
        } else {
            /**
             * Enqueue scripts
             */
            $this->growtype_form_enqueue_validation_scripts();

            /**
             * Initiate scripts
             */
            add_action('wp_footer', function () {
                $this->growtype_form_validation_scripts_init();
                $this->growtype_form_submit_scripts_init();
            }, 99);

            /**
             * Render form
             */
            return $this->render_growtype_general_form($form_data, $form_name);
        }
    }

    /**
     * @return void
     */
    function render_growtype_fields($form, $form_name)
    {
        /**
         * Form main fields
         */
        $main_fields = $form['main_fields'] ?? null;

        /**
         * Form confirmation fields
         */
        $confirmation_fields = $form['confirmation_fields'] ?? null;

        /**
         * Classes
         */
        $form_class = $form['class'] ?? null;

        ob_start();
        ?>

        <div class="growtype-form-wrapper <?= $form_class ?>">
            <div class="growtype-form-fields">
                <?php
                foreach ($form as $key => $form_fields) { ?>

                    <?php if (str_contains('main_fields', $key)) { ?>
                        <div class="row g-3 main-fields">
                            <?php
                            foreach ($main_fields as $field) {
                                self::render_growtype_form_field($field);
                            }
                            ?>
                        </div>
                    <?php } ?>

                    <?php if (str_contains('confirmation_fields', $key)) { ?>
                        <div class="row fields-confirmation">
                            <?php
                            foreach ($confirmation_fields as $field) {
                                self::render_growtype_form_field($field);
                            }
                            ?>
                        </div>
                    <?php } ?>

                <?php } ?>
            </div>
        </div>

        <div>
            <input type="text" hidden name='<?= Growtype_Form_Crud::GROWTYPE_FORM_NAME_IDENTIFICATOR ?>' value="<?= $form_name ?>"/>
        </div>

        <?php

        return ob_get_clean();
    }

    /**
     * @param $form
     * @return false|string|null
     */
    function render_growtype_general_form($form, $form_name)
    {
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
         * Form main fields
         */
        $main_fields = $form['main_fields'] ?? null;

        /**
         * Form confirmation fields
         */
        $confirmation_fields = $form['confirmation_fields'] ?? null;

        /**
         * Form title
         */
        $form_title = $form['title'] ?? null;

        /**
         * Form type
         */
        $form_type = $form['type'] ?? null;

        /**
         * Login btn
         */
        $login_btn = $form['login_btn'] ?? null;

        /**
         * Back btn
         */
        $back_btn = $form['back_btn'] ?? null;

        /**
         * Block class
         */
        $class = $form['class'] ?? null;

        /**
         * Submit row
         */
        $submit_row = $form['submit_row'] ?? null;

        /**
         * Post data
         */
        $post = self::growtype_form_get_current_post();

        ob_start();
        ?>

        <div class="growtype-form-wrapper">

            <?php $this->growtype_form_get_notice(); ?>

            <div class="growtype-form-container">
                <div class="form-wrapper">

                    <div class="b-intro">
                        <?php
                        if ($back_btn) { ?>
                            <a href="<?= growtype_form_login_page_url() ?>" class="btn-back"></a>
                        <?php } ?>

                        <?php if (!empty($form_title)) { ?>
                            <h2 class="e-title-intro"><?php echo $form_title ?></h2>
                        <?php } ?>
                    </div>

                    <form id="growtype-form-<?php echo $form_name ?>" enctype="multipart/form-data" class="growtype-form form <?php echo $class ?>" action="<?php echo self::growtype_form_get_action_url(); ?>" method="post" data-name="<?php echo $form_name ?>">
                        <?php
                        foreach ($form as $key => $form_fields) { ?>

                            <?php if (str_contains('main_fields', $key)) { ?>
                                <div class="row g-3 main-fields">
                                    <?php
                                    foreach ($main_fields as $field) {
                                        self::render_growtype_form_field($field);
                                    }
                                    ?>
                                </div>
                            <?php } ?>

                            <?php if (str_contains('confirmation_fields', $key)) { ?>
                                <div class="row fields-confirmation">
                                    <?php
                                    foreach ($confirmation_fields as $field) {
                                        self::render_growtype_form_field($field);
                                    }
                                    ?>
                                </div>
                            <?php } ?>

                        <?php } ?>

                        <div>
                            <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_SUBMIT_ACTION ?>' value="submit"/>
                            <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID ?>' value="<?= get_current_user_id() ?? null ?>"/>
                            <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_NAME_IDENTIFICATOR ?>' value="<?= $form_name ?>"/>
                            <?php if (!empty($post)) { ?>
                                <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR ?>' value="<?= $post->ID ?>"/>
                            <?php } ?>
                        </div>

                        <?php if (isset($submit_row) && !empty($submit_row)) { ?>
                            <div class="row row-submit <?= isset($submit_row['class']) ? $submit_row['class'] : '' ?>">
                                <div class="d-md-grid gap-2 d-md-flex">
                                    <?php if (!empty($recaptchav3)) { ?>
                                        <div class="g-recaptcha"
                                             data-sitekey="<?= $recaptchav3 ?>"
                                             data-size="invisible"
                                             data-callback="recaptchaFormSubmit">
                                        </div>
                                    <?php } ?>

                                    <?php
                                    if (isset($submit_row['cta'])) {
                                        foreach ($submit_row['cta'] as $cta) { ?>
                                            <button type="<?php echo $cta['type']; ?>" class="<?php echo isset($cta['class']) ? $cta['class'] : 'btn btn-primary'; ?>" data-action="<?php echo isset($cta['action']) ? $cta['action'] : 'submit'; ?>"><?= $cta['label'] ?? __("Save", "growtype-form") ?></button>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button type="submit" class="btn btn-primary" data-action="submit"><?= $form['submit_label'] ?? __("Save", "growtype-form") ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </form>

                    <?php
                    if ($login_btn) { ?>
                        <div class="b-actions">
                            <p><?= __("Do you already have an account?", "growtype-form") ?></p>
                            <a class="btn btn-link" href="<?= growtype_form_login_page_url() ?>"><?= __("Sign in", "growtype-form") ?></a>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>

        <?php

        echo growtype_form_include_view('modals/terms');

        echo growtype_form_include_view('modals/privacy');

        return ob_get_clean();
    }

    /**
     * Form action url
     */
    public static function growtype_form_get_current_post()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $page_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

            if ($page_slug === Growtype_Form_Signup::CUSTOM_SLUG || $page_slug === Growtype_Form_Login::URL_SLUG) {
                return '';
            }
        }

        return !empty(get_post()) && str_contains($_SERVER['REQUEST_URI'], get_post()->post_name) ? get_post() : null;
    }

    /**
     * Form action url
     */
    public static function growtype_form_get_current_post_slug()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $page_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

            if ($page_slug === Growtype_Form_Signup::CUSTOM_SLUG || $page_slug === Growtype_Form_Login::URL_SLUG) {
                return $page_slug;
            }
        }

        return $_SERVER['REQUEST_URI'] ?? home_url();
    }

    /**
     * Form action url
     */
    public static function growtype_form_get_action_url()
    {
        $current_post_slug = self::growtype_form_get_current_post_slug();

        return home_url($current_post_slug);
    }

    /**
     * @param $field
     * @return null
     */
    public static function render_growtype_form_field($field)
    {
        /**
         * Render field
         */
        include GROWTYPE_FORM_PATH . 'includes/methods/components/fields.php';
    }

    /**
     * Required scripts
     */
    function growtype_form_enqueue_validation_scripts()
    {
        wp_enqueue_script('jquery.validate.js', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.16.0/jquery.validate.min.js', '', '', true);

        if (get_locale() === 'lt_LT') {
            wp_enqueue_script('jquery.validate.js.localization', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.16.0/localization/messages_lt.js', '', '', true);
        }
    }

    /**
     * Validate form
     */
    function growtype_form_validation_scripts_init()
    {
        ?>
        <script>
            jQuery.validator.setDefaults({
                ignore: ":hidden:not(.e-wrapper:visible select),.chosen-search-input",
                errorPlacement: function (error, element) {
                    if (element.is(".growtype-form select")) {
                        element.parent().append(error);
                    } else if (element.is(".growtype-form input[type='checkbox']")) {
                        element.parent().append(error);
                    } else if (element.is(".growtype-form .filestyle")) {
                        element.parent().append(error);
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            if ($(".growtype-form select:visible").length > 0) {
                $(".growtype-form select:visible").each(function () {
                    if ($(this).attr('required') !== undefined) {
                        $(this).on("change", function () {
                            $(this).valid();
                        });
                    }
                });
            }
        </script>
        <?php
    }

    /**
     * @return void
     */
    function growtype_form_submit_scripts_init()
    {
        $validation_message = [
            'at_leas_one' => __('At least one selection is required.', 'growtype-form'),
            'wrong_date_format' => __('Wrong date format. Please select again.', 'growtype-form'),
        ];
        ?>
        <script>
            $('.growtype-form button[type="submit"]').click(function () {

                $(this).attr('disabled', false);

                /**
                 * Action
                 */
                let action = $(this).attr('data-action');

                $('input[name="growtype_form_submit_action"]').val(action);

                if (action === 'delete') {
                    $(this).closest('.growtype-form').submit();
                } else {
                    let isValid = $(this).closest(".growtype-form").valid();

                    /**
                     * Check if form check group is valid
                     */
                    if ($(".form-check-wrapper[aria-required='true']").length > 0) {
                        $(".form-check-wrapper[aria-required='true']:visible").each(function () {
                            $(this).find('.error').remove();
                            if ($(this).find("input:checked").length === 0) {
                                $(this).append('<label class="error"><?php echo $validation_message['at_leas_one'] ?></label>');
                                isValid = false;
                            }
                        });
                    }

                    /**
                     * Check if image galleries valid
                     */
                    $('.image-uploader:visible').each(function () {
                        if ($(this).closest('.e-wrapper').attr('data-required') === 'true') {
                            $(this).closest('.e-wrapper').find('.error').remove();
                            if (!$(this).find('.image-uploader-inner').hasClass('has-files')) {
                                $(this).closest('.e-wrapper').append('<label class="error"><?php echo $validation_message['at_leas_one'] ?></label>');
                                isValid = false;
                            }
                        }
                    });

                    /**
                     * Datepicker validate
                     */
                    if ($('.datetimepicker').length > 0) {
                        $('.datetimepicker').each(function () {
                            try {
                                $.datepicker.parseDate('<?= self::DATE_TIME_DATA['date_format'] ?>', $(this).val());
                            } catch (e) {
                                $(this).closest('.e-wrapper').append('<label class="error"><?php echo $validation_message['wrong_date_format'] ?></label>');
                                isValid = false;
                            }
                        });
                    }

                    /**
                     * Stop if is not valid
                     */
                    if (!isValid) {
                        event.preventDefault();
                        return false;
                    }

                    /**
                     * Disable btn
                     */
                    $(this).attr('disabled', true);

                    /**
                     * Submit form
                     */
                    $(this).closest('.growtype-form').submit();
                }
            });
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
            $('.growtype-form').submit(function (event) {
                event.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                grecaptcha.reset();
                grecaptcha.execute();
            });

            function recaptchaFormSubmit(token) {
                document.getElementById("growtype-form").submit();
            }
        </script>
        <?php
    }

    /**
     * @return void
     */
    function update_return_data($post_id, $form_data, $form_name)
    {
        $post = get_post($post_id);

        if ($form_name === 'signup') {
            $signup_data = isset($_COOKIE['signup_data']) ? json_decode(stripslashes($_COOKIE['signup_data']), true) : null;

            if (!empty($signup_data)) {
                foreach ($signup_data as $key => $field) {
                    $_REQUEST[$key] = $field;
                }
            }
        } elseif (!empty($post) && $post->post_type === 'product') {
            $product = wc_get_product($post_id);

            if (empty($product)) {
                return null;
            }

            $extra_meta_keys = $this->get_product_meta_keys(growtype_form_default_product_type());

            $_REQUEST['title'] = $product->get_title();
            $_REQUEST['description'] = $product->get_description();
            $_REQUEST['short_description'] = $product->get_short_description();
            $_REQUEST['featured_image'] = wp_get_attachment_url($product->get_image_id());
            $_REQUEST['_price'] = $product->get_price();

            foreach ($extra_meta_keys as $meta_key) {
                $_REQUEST[$meta_key] = get_post_meta($product->get_id(), $meta_key, true);
            }

            $_REQUEST['categories'] = [];
            foreach ($product->get_category_ids() as $category_id) {
                $term = get_term_by('id', $category_id, 'product_cat');
                if (!empty($term->parent)) {
                    $term_parent = get_term_by('id', $term->parent, 'product_cat');

                    if (isset($_REQUEST['categories'][$term_parent->slug])) {
                        array_push($_REQUEST['categories'][$term_parent->slug], $term->slug);
                    } else {
                        $_REQUEST['categories'][$term_parent->slug] = [$term->slug];
                    }
                }
            }

            /**
             * Set gallery images
             */
            $f_img_id = $product->get_image_id();
            $image_upload_ids = $product->get_gallery_image_ids();

            if (!in_array((int)$f_img_id, $image_upload_ids)) {
                array_unshift($image_upload_ids, (int)$f_img_id);
            }

            $gallery_images = [];
            if (!empty($image_upload_ids)) {
                foreach ($image_upload_ids as $key => $image_id) {
                    $gallery_images[$key] = [
                        'id' => $image_id,
                        'src' => wp_get_attachment_image_url($image_id),
                    ];
                }
            }

            $growtype_form_image_upload_data = [
                'preloaded' => json_encode($gallery_images)
            ];

            /**
             * Add gallery data to js
             */
            wp_localize_script('growtype-form-render', 'growtype_form_image_upload_data', $growtype_form_image_upload_data);

            /**
             * Update shipping details
             */
            if (class_exists('Growtype_Product')) {
                $shipping_documents = Growtype_Product::shipping_documents();
                foreach ($shipping_documents as $document) {
                    if (isset($document['key']) && isset($document['url'])) {
                        $_REQUEST['shipping_documents[' . $document['key'] . ']'] = $document;
                    }
                }
            }
        } elseif (!empty($post) && str_contains($post->post_name, 'account')) {
            $user_id = get_current_user_id();
            if (!empty($user_id) && isset($form_data['main_fields'])) {
                foreach ($form_data['main_fields'] as $field) {
                    if (isset($field['name'])) {
                        $_REQUEST[$field['name']] = get_user_meta($user_id, $field['name'], true);
                    }
                }
            }
        }

        if ($post_id && apply_filters('growtype_form_render_update_return_data', $post_id, $_REQUEST) !== $post_id) {
            $_REQUEST = apply_filters('growtype_form_render_update_return_data', $post_id, $_REQUEST);
        }

        if (isset($_COOKIE['signup_data'])) {
            unset($_COOKIE['signup_data']);
            setcookie('signup_data', null, time(), home_url());
        }
    }
}
