<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin
 * @author     Your Name <email@example.com>
 */
class Growtype_Form_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $Growtype_Form The ID of this plugin.
     */
    private $Growtype_Form;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Growtype_Form The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($Growtype_Form, $version)
    {
        $this->Growtype_Form = $Growtype_Form;
        $this->version = $version;

        if (is_admin()) { // admin actions
            add_action('admin_menu', array ($this, 'admin_menu'));
            add_action('admin_init', array ($this, 'growtype_form_settings_content'));
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Growtype_Form_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Growtype_Form_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->Growtype_Form, plugin_dir_url(__FILE__) . 'css/growtype-form-admin.css', array (), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Growtype_Form_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Growtype_Form_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->Growtype_Form, plugin_dir_url(__FILE__) . 'js/growtype-form-admin.js', array ('jquery'), $this->version, false);

    }

    /**
     * Register the options page with the Wordpress menu.
     */
    function admin_menu()
    {
        add_options_page(
            'Growtype - Form',
            'Growtype - Form',
            'manage_options',
            'growtype-form-settings',
            array ($this, 'growtype_form_settings_form'),
            1
        );
    }

    function growtype_form_settings_form()
    {
        ?>

        <div class="wrap">

            <h1>Growtype - Form settings</h1>

            <form id="growtype_form_main_settings_form" method="post" action="options.php">
                <?php
                settings_fields('growtype_form_settings_id');
                do_settings_sections('growtype-form-settings'); // just a page slug
                submit_button();
                ?>
            </form>

            <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/ace/ace.js"></script>
            <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/ace/theme-twilight.js"></script>
            <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/ace/mode-ruby.js"></script>
            <script src="<?= plugin_dir_url(__FILE__) ?>plugins/jquery-ace/jquery-ace.min.js"></script>

            <script>
                $ = jQuery;
                let forms = $('#growtype_form_main_settings_form').find('.growtype_form_json_content');

                forms.map(function (index, value) {
                    if ($(value).length > 0) {
                        let editor = $(value).ace({
                            theme: 'twilight',
                            lang: 'ruby'
                        })

                        let growtype_form_json_content = $(value).data('ace');
                        growtype_form_json_content.editor.ace.setValue(JSON.stringify(JSON.parse($(value).text()), null, '\t'));
                    }
                });

                if ($('body').hasClass('settings_page_growtype-form-settings')) {
                    $('#growtype_form_main_settings_form input[type="submit"]').click(function () {
                        let forms = $(this).closest('#growtype_form_main_settings_form').find('.growtype_form_json_content');

                        forms.map(function (index, value) {
                            if ($(value).length > 0) {
                                try {
                                    JSON.parse($(value).data('ace').editor.ace.getValue())
                                } catch (e) {
                                    alert("Caught: " + e.message)
                                    event.preventDefault();
                                }
                            }
                        });
                    });
                }
            </script>
        </div>

        <?php
    }

    /**
     *
     */
    function growtype_form_settings_content()
    {
        add_settings_section(
            'growtype_form_settings_id', // section ID
            'Form settings', // title (if needed)
            '', // callback function (if needed)
            'growtype-form-settings' // page slug
        );

        /**
         * WooCommerce Product Upload Json Content
         */
        register_setting(
            'growtype_form_settings_id', // settings group name
            'wc_product_upload_form_json_content' // option name
        );

        add_settings_field(
            'wc_product_upload_form_json_content',
            'WooCommerce Product Upload Json Content',
            array ($this, 'wc_product_upload_form_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_id'
        );

        /**
         * Upload post
         */
        register_setting(
            'growtype_form_settings_id', // settings group name
            'upload_post_form_json_content' // option name
        );

        add_settings_field(
            'upload_post_form_json_content',
            'Post Upload Json Content',
            array ($this, 'upload_post_form_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_id'
        );

        /**
         * growtype_form_json_content
         */
        register_setting(
            'growtype_form_settings_id', // settings group name
            'growtype_form_json_content' // option name
        );

        add_settings_field(
            'growtype_form_json_content',
            'General Form Json Content',
            array ($this, 'growtype_form_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_id'
        );
    }

    /**
     * Wc upload product
     */
    function wc_product_upload_form_json_content_callback()
    {
        ?>
        <textarea id="wc_product_upload_form_json_content" class="growtype_form_json_content" name="wc_product_upload_form_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;">
            <?= get_option('wc_product_upload_form_json_content') ?>
        </textarea>
        <?php
    }

    /**
     * Upload post
     */
    function upload_post_form_json_content_callback()
    {
        ?>
        <textarea id="upload_post_form_json_content" class="growtype_form_json_content" name="upload_post_form_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;">
            <?= get_option('upload_post_form_json_content') ?>
        </textarea>
        <?php
    }

    /**
     * General form
     */
    function growtype_form_json_content_callback()
    {
        ?>
        <textarea id="growtype_form_json_content" class="growtype_form_json_content" name="growtype_form_json_content" rows="40" cols="100" style="width: 100%;">
            <?= get_option('growtype_form_json_content') ?>
        </textarea>
        <?php
    }
}
