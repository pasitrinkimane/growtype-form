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
    const SETTINGS_DEFAULT_TAB = 'general';

    const SETTINGS_PAGE_SLUG = 'growtype-form-settings';

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $growtype_form The ID of this plugin.
     */
    private $growtype_form;

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
     * @param string $growtype_form The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($growtype_form, $version)
    {
        $this->growtype_form = $growtype_form;
        $this->version = $version;

        if (is_admin()) {
            /**
             * Load admin methods
             */
            $this->load_admin_methods();
        } else {
            /**
             * Growtype form menu links update
             */
            add_filter('walker_nav_menu_start_el', array ($this, 'update_growtype_form_frontend_menu_links'), 10, 4);
        }

        /**
         * Load global methods
         */
        $this->load_methods();

        /**
         * Login Enqueue styles
         */
        add_action('login_enqueue_scripts', array ($this, 'login_enqueue_scripts_callback'));

        /**
         * Show notices
         */
        add_action('admin_notices', array ($this, 'show_admin_notice'));
    }

    function login_enqueue_scripts_callback()
    {
        wp_enqueue_style('growtype-form-login', plugin_dir_url(__FILE__) . 'css/growtype-form-login.css', array (), GROWTYPE_FORM_VERSION, 'all');
    }

    /**
     * @param $output
     * @param $item
     * @param $depth
     * @param $args
     * @return array|string|string[]
     */
    function update_growtype_form_frontend_menu_links($output, $item, $depth, $args)
    {
        $output = str_replace('#growtype_form_logout_url#', wp_logout_url(home_url()), $output);

        return $output;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->growtype_form, plugin_dir_url(__FILE__) . 'css/growtype-form-admin.css', array (), GROWTYPE_FORM_VERSION, 'all');
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

        wp_enqueue_script($this->growtype_form, plugin_dir_url(__FILE__) . 'js/growtype-form-admin.js', array ('jquery'), $this->version, false);

    }

    private function load_methods()
    {
        /**
         * Admin pages
         */
        require GROWTYPE_FORM_PATH . '/admin/pages/class-growtype-form-admin-page.php';
        new Growtype_Form_Admin_Page();
    }

    /**
     * Load the required methods for this plugin.
     *
     */
    private function load_admin_methods()
    {
        /**
         * Appearance
         */
        require_once GROWTYPE_FORM_PATH . 'admin/methods/appearance/class-growtype-form-admin-appearance.php';
        new Growtype_Form_Admin_Appearance();

        /**
         * Accesses
         */
        require_once GROWTYPE_FORM_PATH . 'admin/methods/users/class-growtype-form-user-accesses.php';
        new Growtype_Form_User_Accesses();

        /**
         * Signup users
         */
        if (get_option('growtype_form_settings_signups_enabled')) {
            /**
             * Load users
             */
            if (!class_exists('WP_List_Table')) {
                require(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
            }

            require_once(ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php');

            /**
             * Signup details
             */
            require_once GROWTYPE_FORM_PATH . 'admin/methods/users/class-growtype-form-signup-details.php';
            require_once GROWTYPE_FORM_PATH . 'admin/methods/users/class-growtype-form-signups-list-table.php';
            new Growtype_Form_Signup_Details();
        }
    }

    public static function init_json_editor($element = '.growtype_form_json_content', $additional_params = null)
    {
        $params = [
            'theme' => 'twilight',
            'lang' => 'ruby'
        ];

        if (isset($additional_params['height'])) {
            $params['height'] = $additional_params['height'];
        }

        if (isset($additional_params['width'])) {
            $params['width'] = $additional_params['width'];
        }

        ?>
        <script src="<?= GROWTYPE_FORM_URL ?>admin/plugins/jquery-ace/ace/ace.js"></script>
        <script src="<?= GROWTYPE_FORM_URL ?>admin/plugins/jquery-ace/ace/theme-twilight.js"></script>
        <script src="<?= GROWTYPE_FORM_URL ?>admin/plugins/jquery-ace/ace/mode-ruby.js"></script>
        <script src="<?= GROWTYPE_FORM_URL ?>admin/plugins/jquery-ace/jquery-ace.min.js"></script>

        <script>
            $ = jQuery;
            let forms = $('<?php echo $element ?>');

            let aceParams = <?php echo json_encode($params) ?>;

            forms.map(function (index, value) {
                if ($(value).length > 0) {
                    let editor = $(value).ace(aceParams)

                    let growtype_form_json_content = $(value).data('ace');
                    if (growtype_form_json_content.element.length > 0) {
                        if ($(value).text()) {
                            growtype_form_json_content.editor.ace.setValue(JSON.stringify(JSON.parse($(value).text()), null, '\t'));
                        }
                    }
                }
            });

            $('.wp-admin input[type="submit"]').click(function () {
                let forms = $('.growtype_form_json_content');
                forms.map(function (index, value) {
                    if ($(value).data('ace').editor.ace.getValue().length > 0) {
                        try {
                            JSON.parse($(value).data('ace').editor.ace.getValue())
                        } catch (e) {
                            alert("Caught: " + e.message)
                            event.preventDefault();
                        }
                    }
                });
            });
        </script>
        <?php
    }

    public static function set_notices($notices_details)
    {
        set_transient('growtype_form_admin_notices', $notices_details, 60);
    }

    public static function update_notices($notices_details)
    {
        $existing_notices_details = get_transient('growtype_form_admin_notices');
        $notices_details = !empty($notices_details) ? $notices_details : [];
        $existing_notices_details = !empty($existing_notices_details) ? $existing_notices_details : [];
        $existing_notices_details = array_merge($existing_notices_details, $notices_details);
        self::set_notices($existing_notices_details);
    }

    function show_admin_notice()
    {
        $notices_details = get_transient('growtype_form_admin_notices');

        if (!empty($notices_details)) {
            foreach ($notices_details as $notice_details) {
                if (isset($notice_details['message']) && !empty($notice_details['message'])) {
                    echo '<div class="notice ' . ($notice_details['success'] ? 'notice-success' : 'notice-error') . ' is-dismissible"><p>' . esc_html($notice_details['message']) . '</p></div>';
                }
            }

            delete_transient('growtype_form_admin_notices');
        }
    }
}
