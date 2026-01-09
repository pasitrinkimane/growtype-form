<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_General
{
    use GrowtypeFormAuth;
    use GrowtypeFormFile;
    use GrowtypeFormProduct;

    const SHORTCODE_NAME = 'growtype_form';
    const IMAGE_UPLOADER_OLD_IMAGES_PREFIX = 'image_uploader_old';

    const ALLOWED_FIELD_TYPES = [
        'text',
        'textarea',
        'file',
        'email',
        'select',
        'radio',
        'checkbox',
        'hidden',
        'number',
        'password',
        'custom',
        'fully_custom',
        'repeater',
        'shortcode',
        'tel',
        'url'
    ];

    public function __construct()
    {
        if (!is_admin()) {
            add_shortcode(self::SHORTCODE_NAME, array ($this, 'growtype_form_shortcode_function'));
        }
    }

    public static function get_date_time_data()
    {
        $iso_dates = [
            'Y-m-d' => 'yy-mm-dd',
            'd-m-Y' => 'dd-mm-yy'
        ];

        return [
            'date_format' => get_option('date_format'),
            'date_format_iso' => isset($iso_dates[get_option('date_format')]) ? $iso_dates[get_option('date_format')] : 'dd-mm-yy',
        ];
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
        wp_enqueue_style('growtype-form-render', GROWTYPE_FORM_URL_PUBLIC . 'styles/growtype-form-render.css', array (), GROWTYPE_FORM_VERSION, 'all');

        /**
         * Styles
         */
        if (!wp_script_is('image-uploader', 'enqueued')) {
            wp_enqueue_style('image-uploader', GROWTYPE_FORM_URL_PUBLIC . 'plugins/image-uploader/image-uploader.css', array (), '1.0', 'all');
        }

        /**
         * Jquery UI
         */
        if (!wp_script_is('jquery-ui', 'enqueued')) {
            wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css', array (), '1.0', 'all');

            /**
             * Timepicker
             */
            if (!wp_script_is('jquery-ui-timepicker-addon.min.css', 'enqueued')) {
                wp_enqueue_style('jquery-ui-timepicker-addon.min.css', GROWTYPE_FORM_URL_PUBLIC . 'plugins/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css', array (), '1.1', 'all');
            }
        }

        /**
         * Auth styles
         */
        if (strpos($form_name, 'signup') !== false || strpos($form_name, 'login') !== false) {
            wp_enqueue_style('growtype-form-auth', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/auth/index.css', array (), GROWTYPE_FORM_VERSION, 'all');
        }

        do_action('growtype_form_render_styles');
    }

    /**
     * Required scripts
     */
    function growtype_form_enqueue_render_scripts()
    {
        wp_enqueue_script('growtype-form-render', GROWTYPE_FORM_URL_PUBLIC . 'scripts/growtype-form-render.js', array ('jquery'), GROWTYPE_FORM_VERSION, true);

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
            wp_enqueue_script('image-uploader', GROWTYPE_FORM_URL_PUBLIC . 'plugins/image-uploader/image-uploader.js', array ('jquery'), '1.22', true);
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

            if (!wp_script_is('jquery-ui-timepicker-addon.min.js', 'enqueued')) {
                wp_enqueue_script('jquery-ui-timepicker-addon.min.js', GROWTYPE_FORM_URL_PUBLIC . 'plugins/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js', array ('jquery'), '1.1', true);
            }
        }

        /**
         * Autonumeric
         */
        if (!wp_script_is('autoNumeric', 'enqueued')) {
            wp_enqueue_script('autoNumeric', GROWTYPE_FORM_URL_PUBLIC . 'plugins/autoNumeric/autoNumeric.min.js', array ('jquery'), '1.1', true);
        }

        do_action('growtype_form_render_scripts');
    }

    public static function get_currency_data()
    {
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

        /**
         * Decode currency symbol
         */
        $currency_symbol = html_entity_decode($currency_symbol, ENT_QUOTES, "utf-8");

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

        return array (
            'currencySymbolPlacement' => $currency_symbol_placement,
            'digitGroupSeparator' => $digitGroupSeparator,
            'decimalCharacter' => $decimalCharacter,
            'currencySymbol' => $currency_symbol,
            'decimalPlacesOverride' => $decimalPlacesOverride,
        );
    }

    /**
     * Upload form shortcode
     * [growtype_form name="{form_name}"]
     */
    public function growtype_form_shortcode_function($args)
    {
        /**
         * If empty shortcode arguments, return empty
         */
        if (empty($args)) {
            return '';
        }

        $args = apply_filters('growtype_form_shortcode_args', $args);

        /**
         * Check if login required
         */
        if (isset($args['login_required']) && filter_var($args['login_required'], FILTER_VALIDATE_BOOLEAN) && !is_user_logged_in()) {
            wp_redirect(
                add_query_arg([
                    'redirect_after' => get_permalink(),
                ], growtype_form_login_page_url())
            );
            die();
        }

        add_action('wp_footer', array ($this, 'growtype_form_show_hide_password_button'), 100);

        ob_start();

        if (!is_admin()) {
            echo Growtype_Form_Notice::growtype_form_get_notice();
        }

        echo $this->form_init($args);

        return ob_get_clean();
    }

    public function form_init($args)
    {
        /**
         * Set redirect to cookie
         */
        $redirect_after = isset($_GET['redirect_after']) && !empty($_GET['redirect_after']) ? wp_validate_redirect($_GET['redirect_after'], home_url()) : null;
        $redirect_after = empty($redirect_after) && isset($args['redirect_after']) && !empty($args['redirect_after']) ? $args['redirect_after'] : $redirect_after;

        if (!empty($redirect_after)) {
            setcookie('growtype_form_redirect_after', $redirect_after, time() + 120, COOKIEPATH, COOKIE_DOMAIN);
        }

        /**
         * Init page body classes
         */
        add_filter('body_class', array ($this, 'growtype_form_page_body_class'));

        /**
         * Form name
         */
        $form_name = $args['name'] ?? '';
        $form_name = self::format_form_name($form_name);
        $form_type = isset($args['type']) ? $args['type'] : null;
        $form_action = isset($args['action']) ? $args['action'] : 'submit';

        if (isset($args['form_data']) && !empty($args['form_data'])) {
            $form_data = json_decode(base64_decode($args['form_data']), true);
        }

        /**
         * Get form data
         */
        $form_data = isset($form_data) && !empty($form_data) ? $form_data : Growtype_Form_Crud::get_growtype_form_data($form_name);

        if (empty($form_data)) {
            return '<p style="text-align: center;color:red;">' . __('Form is not configured. Please contact site admin. (Sometimes need to save form in settings)', 'growtype-form') . '</p>';
        }

        /**
         * Add args
         */
        $form_data['args'] = $args;

        /**
         * Overwrite form data with existing extra arguments, if they exist
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

        /**
         * Init form body classes
         */
        if (strpos($form_name, 'signup') !== false) {
            add_filter('body_class', array ($this, 'growtype_form_signup_body_class'));
        } elseif (strpos($form_name, 'login') !== false) {
            add_filter('body_class', array ($this, 'growtype_form_login_body_class'));
        }

        /**
         * Enqueue render styles
         */
        $this->growtype_form_enqueue_render_styles($form_name);

        /**
         * Enqueue render scripts
         */
        $this->growtype_form_enqueue_render_scripts();

        /**
         * Check if edit page and update post request data
         */
        $this->update_return_data(get_the_ID(), $form_data, $form_name);

        /**
         * Enqueue validation scripts
         */
        $this->growtype_form_enqueue_validation_scripts();

        /**
         * Initiate scripts
         */
        add_action('wp_footer', array (__CLASS__, 'growtype_form_validation_scripts_init'), 100);

        /**
         * Render forms
         * $form_type Fields - plain fields with no extra features
         */
        if ($form_type === 'fields') {
            return $this->render_fields($form_data, $form_name);
        } elseif (strpos($form_name, 'login') !== false) {
            add_action('wp_footer', array (__CLASS__, 'growtype_form_login_validation_scripts'), 100);

            return Growtype_Form_Login::render_login_form($form_data);
        } else {
            add_action('wp_footer', array (__CLASS__, 'growtype_form_submit_scripts_init'), 101);
            return $this->render_form($form_name, $form_data, $form_action);
        }
    }

    public static function format_form_name($form_name)
    {
        return preg_replace('/\s+/', '_', strtolower($form_name));
    }

    /**
     * @return void
     */
    public function render_fields($form_data, $form_name)
    {
        /**
         * Form main fields
         */
        $main_fields = isset($form_data['main_fields']) ? $form_data['main_fields'] : [];

        /**
         * Form confirmation fields
         */
        $confirmation_fields = isset($form_data['confirmation_fields']) ? $form_data['confirmation_fields'] : [];

        /**
         * Style
         */
        $form_style = $form_data['args']['style'] ?? $form_data['style'] ?? '';

        /**
         * Classes
         */
        $wrapper_classes = self::wrapper_classes($form_data);

        /**
         * Render
         */
        ob_start();
        ?>

        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)) ?>" style="<?php echo $form_style ?>" data-name="<?php echo esc_attr($form_name) ?>">
            <div class="growtype-form-fields">
                <?php
                foreach ($form_data as $key => $form_fields) { ?>

                    <?php if (strpos('main_fields', $key) !== false) { ?>
                        <div class="row g-3 main-fields">
                            <?php
                            foreach ($main_fields as $field) {
                                self::render_growtype_form_field($field);
                            }
                            ?>
                        </div>
                    <?php } ?>

                    <?php if (strpos('confirmation_fields', $key) !== false) { ?>
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

    public static function wrapper_classes($form_data)
    {
        $wrapper_classes = ['growtype-form-wrapper'];

        if (isset($form_data['args']['class'])) {
            array_push($wrapper_classes, $form_data['args']['class']);
        }

        if (isset($form_data['class'])) {
            array_push($wrapper_classes, $form_data['class']);
        }

        return $wrapper_classes;
    }

    /**
     * @param $form
     * @return false|string|null
     */
    function render_form($form_name, $form_data, $form_action = 'submit')
    {
        /**
         * Recaptcha setup
         */
        $recaptcha = $form_data['recaptcha'] ?? null;
        $recaptcha_key = $recaptcha['api_key'] ?? null;

        if (!empty($recaptcha_key) && !function_exists('recaptcha_setup')) {
            add_action('wp_footer', array (__CLASS__, 'recaptcha_setup'), 100);
        }

        /**
         * Render modals
         */
        add_action('wp_footer', array (__CLASS__, 'growtype_form_modals_render'), 100);

        /**
         * Form main fields
         */
        $form_args = growtype_form_extract_form_args($form_data);

        $form_args['form_action'] = $form_action;

        if ($form_action === 'update') {
            foreach ($form_data['submit_row']['cta'] as $key => $cta) {
                if ($cta['type'] = 'submit') {
                    $form_args['submit_row']['cta'][$key]['label'] = __('Update', 'growtype-form');
                }
            }

            $form_args = growtype_form_fill_form_args_with_existing_data($form_args);
        }

        /**
         * Post data
         */
        $growtype_form_post_identificator = $form_data['args']['post_id'] ?? '';

        if (isset($_GET['customize']) && $_GET['customize'] === 'edit' && empty($growtype_form_post_identificator) && Growtype_Wc_Product::user_has_uploaded_product(get_the_ID())) {
            $growtype_form_post_identificator = get_the_ID();
        }

        if (empty($growtype_form_post_identificator)) {
            $post = self::growtype_form_get_current_post();
            $growtype_form_post_identificator = $post->ID ?? null;
        }

        $growtype_form_post_identificator = apply_filters('growtype_form_post_identificator', $growtype_form_post_identificator);

        /**
         * Style
         */
        $form_wrapper_styles = $form_data['args']['style'] ?? $form_data['style'] ?? '';

        $background = isset($form_data['args']['background']) ? $form_data['args']['background'] : null;
        $padding = isset($form_data['args']['padding']) ? $form_data['args']['padding'] : null;

        if (!empty($background) && $background !== 'none') {
            $form_wrapper_styles .= 'background: ' . $background . ';';
        }

        if ($padding !== null) {
            $form_wrapper_styles .= 'padding: ' . $padding . ';';
        }

        /**
         * Classes
         */
        $wrapper_classes = self::wrapper_classes($form_data);

        /**
         * Render
         */
        ob_start();
        ?>

        <div class="<?php echo implode(' ', $wrapper_classes) ?>" data-name="<?php echo $form_name ?>">

            <?php if (isset($form_args['logo']) && isset($form_args['logo']['url']) && !empty($form_args['logo']['url'])) { ?>
                <div class="logo-wrapper">
                    <a href="<?php echo isset($form_args['logo']['external_url']) ? growtype_form_string_replace_custom_variable($form_args['logo']['external_url']) : '#' ?>" class="e-logo">
                        <img src="<?php echo growtype_form_string_replace_custom_variable($form_args['logo']['url']) ?>" class="img-fluid" width="<?php echo $form_args['logo']['width'] ?? '' ?>" height="<?php echo $form_args['logo']['height'] ?? '' ?>"/>
                    </a>
                </div>
            <?php } ?>

            <div class="growtype-form-container">
                <div class="form-wrapper" style="<?php echo $form_wrapper_styles ?>">
                    <?php echo growtype_form_include_view('components.forms.partials.header', ['form_args' => $form_args]) ?>

                    <div class="form-inner-wrapper">
                        <form id="growtype-form-<?php echo $form_name ?>" enctype="multipart/form-data" class="growtype-form form <?php echo $form_args['class'] ?>" action="<?php echo self::growtype_form_get_action_url(); ?>" method="post" data-name="<?php echo $form_name ?>" data-ajax="<?php echo $form_args['ajax'] ?>" data-ajax-action="<?php echo $form_args['ajax_action'] ?>">
                            <?php foreach ($form_data as $key => $form_fields) { ?>
                                <?php if (isset($form_args['main_fields']) && !empty($form_args['main_fields']) && strpos('main_fields', $key) !== false) { ?>
                                    <div class="row g-3 main-fields">
                                        <?php foreach ($form_args['main_fields'] as $field) {
                                            self::render_growtype_form_field($field);
                                        }
                                        ?>
                                    </div>
                                <?php } ?>

                                <?php if (strpos('confirmation_fields', $key) !== false) { ?>
                                    <div class="row fields-confirmation">
                                        <?php foreach ($form_args['confirmation_fields'] as $field) {
                                            self::render_growtype_form_field($field);
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                            <div>
                                <?php
                                wp_nonce_field('growtype_form_general', 'growtype_form_nonce');
                                ?>

                                <?php foreach (Growtype_Form_Crud::GROWTYPE_FORM_SPAM_IDENTIFICATION_RULES as $rule) { ?>
                                    <input type="<?php echo $rule['type'] ?>" <?php echo $rule['hidden'] ? 'hidden' : '' ?> name='<?php echo $rule['key'] ?>' value="" style="<?php echo $rule['style'] ?>"/>
                                <?php } ?>

                                <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_SUBMIT_ACTION ?>' value="<?php echo $form_action ?>"/>

                                <?php
                                $current_user_id = get_current_user_id();

                                if (!empty($current_user_id)) { ?>
                                    <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID ?>' value="<?php echo $current_user_id ?>"/>
                                <?php } ?>

                                <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_NAME_IDENTIFICATOR ?>' value="<?php echo $form_name ?>"/>

                                <?php if (!empty($growtype_form_post_identificator)) { ?>
                                    <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR ?>' value="<?php echo $growtype_form_post_identificator ?>"/>
                                <?php } ?>

                                <?php if (isset($form_data['args']['purpose'])) { ?>
                                    <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_PURPOSE ?>' value="<?php echo $form_data['args']['purpose'] ?>"/>
                                <?php } ?>

                                <?php if (isset($form_data['args']['form_data'])) { ?>
                                    <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_FORM_DATA ?>' value="<?php echo $form_data['args']['form_data'] ?>"/>
                                <?php } ?>

                                <?php if (isset($form_args['redirect_after']) && !empty($form_args['redirect_after'])) { ?>
                                    <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_REDIRECT_AFTER ?>' value="<?php echo $form_args['redirect_after'] ?>"/>
                                <?php } ?>

                                <?php if (class_exists('Growtype_Quiz')) {
                                    $growtype_quiz_unique_hash = growtype_quiz_get_unique_hash();

                                    if (!empty($growtype_quiz_unique_hash)) { ?>
                                        <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_QUIZ_UNIQUE_HASH ?>' value="<?php echo $growtype_quiz_unique_hash ?>"/>
                                    <?php } ?>
                                <?php } ?>

                                <?php if (class_exists('QTX_Translator')) { ?>
                                    <input type="text" hidden name='<?php echo Growtype_Form_Crud::GROWTYPE_FORM_LANGUAGE ?>' value="<?php echo qtranxf_getLanguage() ?>"/>
                                <?php } ?>
                            </div>

                            <?php if (isset($form_args['submit_row']) && !empty($form_args['submit_row'])) { ?>
                                <div class="row row-submit <?= isset($form_args['submit_row']['class']) ? $form_args['submit_row']['class'] : '' ?>">
                                    <div class="row-submit-inner d-md-grid gap-2 d-md-flex">
                                        <?php if (!empty($recaptchav3)) { ?>
                                            <div class="g-recaptcha"
                                                 data-sitekey="<?= $recaptchav3 ?>"
                                                 data-size="invisible"
                                                 data-callback="recaptchaFormSubmit">
                                            </div>
                                        <?php } ?>

                                        <?php if (isset($form_args['submit_row']['cta'])) {
                                            foreach ($form_args['submit_row']['cta'] as $cta) { ?>
                                                <button type="<?php echo $cta['type']; ?>" class="<?php echo isset($cta['class']) ? $cta['class'] : 'btn btn-primary'; ?>" data-action="<?php echo isset($cta['action']) ? $cta['action'] : $form_action; ?>" <?= isset($cta['action']) && $cta['action'] === 'delete' ? 'formnovalidate' : '' ?>><?php echo __($cta['label'], 'growtype-form') ?? __("Save", "growtype-form") ?></button>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button type="submit" class="btn btn-primary" data-action="submit"><?= $form_data['submit_label'] ?? __("Save", "growtype-form") ?></button>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </form>
                    </div>

                    <?php echo growtype_form_include_view('components.forms.partials.footer', ['form_args' => $form_args]) ?>
                </div>
            </div>

            <?php if (user_can(wp_get_current_user(), 'administrator')) { ?>
                <div style="text-align: right;margin-top: 15px;">
                    <a href="<?php echo admin_url() . 'options-general.php?page=growtype-form-settings&tab=post'; ?>" style="display: inline-block;margin-left: auto;position: relative;text-decoration: none;" target="_blank">Edit <span style="color: inherit;" class="dashicons dashicons-edit"></span></a>
                </div>
            <?php } ?>
        </div>

        <?php
        $form_html = ob_get_clean();

        return apply_filters('growtype_form_render_form', $form_html, $form_name, $form_data, $form_action);
    }

    public static function growtype_form_modals_render()
    {
        echo growtype_form_include_view('modals/privacy');
        echo growtype_form_include_view('modals/terms');
    }

    /**
     * Form action url
     */
    public static function growtype_form_get_current_post()
    {
        $request_uri = $_SERVER['REQUEST_URI'] ?? null;

        if (!empty($request_uri)) {
            $page_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

            if ($page_slug === Growtype_Form_Signup::URL_PATH || $page_slug === Growtype_Form_Login::URL_PATH) {
                return '';
            }
        }

        $post = get_post();

        return !empty($post) && !empty($post->post_title) && !empty($request_uri) && strpos($request_uri, $post->post_title) !== false ? $post : null;
    }

    /**
     * Form action url
     */
    public static function growtype_form_get_current_post_slug()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $page_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

            if ($page_slug === Growtype_Form_Signup::URL_PATH || $page_slug === Growtype_Form_Login::URL_PATH) {
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
        $current_post_slug = str_replace('/login/', '/signup/', $current_post_slug);

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
        include GROWTYPE_FORM_PATH . 'includes/methods/components/fields/index.php';
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
    public static function growtype_form_validation_scripts_init()
    {
        ?>
        <script>
            if (typeof jQuery.validator !== 'undefined') {
                jQuery.validator.setDefaults({
                    errorClass: "error error-label",
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
            }
        </script>
        <?php
    }

    /**
     * Validate form
     */
    public static function growtype_form_login_validation_scripts()
    {
        ?>
        <script>
            if (typeof jQuery.fn.validate !== 'undefined') {
                jQuery('form[name="loginform-custom"]').validate();
            }
        </script>
        <?php
    }

    /**
     * @return void
     */
    public static function growtype_form_submit_scripts_init()
    {
        $validation_message = Growtype_Form_Crud::validation_messages();
        ?>
        <script>
            $ = jQuery;

            if (typeof $.validator !== 'undefined') {
                let allowSimplePassword = "<?php echo Growtype_Form_Crud::simple_password_is_allowed() ?>";

                $.validator.addMethod("containsNumber", function (value, element) {
                    return /[0-9]/.test(value);
                }, "<?php echo $validation_message['password_contains_number'] ?>");

                $.validator.addMethod("containsUppercase", function (value, element) {
                    return /[A-Z]/.test(value);
                }, "<?php echo $validation_message['password_contains_uppercase'] ?>");

                $.validator.addMethod("containsLowercase", function (value, element) {
                    return /[a-z]/.test(value);
                }, "<?php echo $validation_message['password_contains_lowercase'] ?>");

                let validationParams = {
                    rules: {
                        password: {
                            required: true,
                        },
                        repeat_password: {
                            required: true,
                            equalTo: "#password"
                        }
                    },
                    messages: {
                        password: {
                            required: "<?php echo $validation_message['password_required'] ?>",
                            minlength: "<?php echo $validation_message['password_min_length'] ?>",
                        },
                        repeat_password: {
                            required: "<?php echo $validation_message['repeat_password'] ?>",
                            equalTo: "<?php echo $validation_message['passwords_not_match'] ?>"
                        }
                    }
                };

                if (!allowSimplePassword) {
                    validationParams.rules.password.minlength = "<?php echo Growtype_Form_Crud::password_min_length() ?>";
                    validationParams.rules.password.containsNumber = true;
                    validationParams.rules.password.containsUppercase = true;
                    validationParams.rules.password.containsLowercase = true;
                }

                /**
                 * Setup validation
                 */
                $('.growtype-form').validate(validationParams);
            }

            /**
             * Form submit
             */
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
                    let isValid = true;

                    if (typeof $(this).closest(".growtype-form").valid === 'function') {
                        isValid = $(this).closest(".growtype-form").valid();
                    }

                    /**
                     * Check if form check group is valid
                     */
                    if ($(".form-check-wrapper[aria-required='true']").length > 0) {
                        $(".form-check-wrapper[aria-required='true']:visible").each(function () {
                            $(this).find('.error').remove();
                            if ($(this).find("input:checked").length === 0) {
                                $(this).append('<label class="error"><?php echo $validation_message['at_leas_one_selection'] ?></label>');
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
                                $(this).closest('.e-wrapper').append('<label class="error"><?php echo $validation_message['at_leas_one_selection'] ?></label>');
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
                                $.datepicker.parseDate('<?= self::get_date_time_data()["date_format_iso"] ?>', $(this).val());
                            } catch (e) {
                                $(this).closest('.e-wrapper').find('.error').remove();
                                $(this).closest('.e-wrapper').append('<label class="error"><?php echo $validation_message['wrong_date_format'] ?></label>');
                                isValid = false;
                            }
                        });
                    }

                    /**
                     * Stop if is not valid
                     */
                    if (!isValid) {
                        if ($('.error:visible').length > 0) {
                            $([document.documentElement, document.body]).animate({
                                scrollTop: $('.error:visible').last().offset().top - ($(window).height() / 2)
                            }, 100);
                        }

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

            if (localStorage.getItem('growtype_quiz_unique_hash')) {
                $('input[name="growtype_quiz_unique_hash"]').val(localStorage.getItem('growtype_quiz_unique_hash'));
            }
        </script>
        <?php

        do_action('growtype_form_submit_scripts_init');
    }

    /**
     * @param $recaptcha_key
     */
    public static function recaptcha_setup($recaptcha_key)
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
                    $field_details = growtype_form_get_form_field($form_name, $key);

                    if (!empty($field_details)) {
                        if ($field_details['type'] === 'select') {
                            $_REQUEST[$key] = $field;
                        } else {
                            $_REQUEST[$key] = $field;
                        }
                    }
                }
            }

        } elseif (!empty($post) && $post->post_type === 'product' && class_exists('woocommerce')) {
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
            $wc_gallery_ids = $product->get_gallery_image_ids();

            if (!in_array((int)$f_img_id, $wc_gallery_ids)) {
                array_unshift($wc_gallery_ids, (int)$f_img_id);
            }

            $image_uploader_ids = [
                'gallery' => $wc_gallery_ids
            ];

            /**
             * Update shipping details
             */
            if (class_exists('Growtype_Wc_Product')) {
                $shipping_documents = Growtype_Wc_Product::shipping_documents();
                foreach ($shipping_documents as $document) {
                    if (isset($document['key']) && isset($document['url'])) {
                        $_REQUEST['shipping_documents[' . $document['key'] . ']'] = $document;
                    }
                }
            }
        } elseif (!empty($post) && strpos($post->post_name, 'account') !== false) {
            $user_id = get_current_user_id();
            if (!empty($user_id) && isset($form_data['main_fields'])) {
                foreach ($form_data['main_fields'] as $field) {
                    if (isset($field['name'])) {
                        $_REQUEST[$field['name']] = get_user_meta($user_id, $field['name'], true);
                    }
                }
            }
        }

        $image_uploader_ids = $image_uploader_ids ?? $form_data['args']['image_uploader_ids'] ?? [];

        $image_uploader_ids = apply_filters('growtype_form_render_update_return_data_image_uploader_ids', $image_uploader_ids, $post_id, $form_data, $form_name);

        $image_uploaders_images = [];
        if (!empty($image_uploader_ids)) {
            foreach ($image_uploader_ids as $key => $ids) {
                if (!empty($ids)) {
                    foreach ($ids as $image_id) {
                        $image_uploaders_images[$key][] = [
                            'id' => $image_id,
                            'src' => wp_get_attachment_image_url($image_id),
                        ];
                    }
                }
            }
        }

        if (!empty($image_uploaders_images)) {
            $growtype_form_image_uploaders = [
                'content' => json_encode($image_uploaders_images),
                'old_images_prefix' => self::IMAGE_UPLOADER_OLD_IMAGES_PREFIX,
            ];

            $_REQUEST['growtype_form_image_uploaders'] = $growtype_form_image_uploaders;

            /**
             * Add gallery data to js
             */
            wp_localize_script('growtype-form-render', 'growtype_form_image_uploaders', $growtype_form_image_uploaders);
        }

        if ($post_id && apply_filters('growtype_form_render_update_return_data', $post_id, $_REQUEST) !== $post_id) {
            $_REQUEST = apply_filters('growtype_form_render_update_return_data', $post_id, $_REQUEST);
        }

//        if (isset($_COOKIE['signup_data'])) {
//            unset($_COOKIE['signup_data']);
//            setcookie('signup_data', '', time(), COOKIEPATH, COOKIE_DOMAIN);
//        }
    }

    public static function image_uploader_get_old_images_key($group_key)
    {
        return Growtype_Form_General::IMAGE_UPLOADER_OLD_IMAGES_PREFIX . '_' . $group_key;
    }

    public static function render_custom_form($form_name, $form_data)
    {
        return do_shortcode('[growtype_form name="' . $form_name . '" form_data="' . base64_encode(json_encode($form_data)) . '"]');
    }
}
