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
    const GROWTYPE_FORM_SETTINGS_DEFAULT_TAB = 'login';

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
     * Traits
     */
    use AdminSettingCredentials;
    use AdminSettingGeneral;
    use AdminSettingsLogin;
    use AdminSettingsSignup;
    use AdminSettingsWoocommercePlugin;
    use AdminSettingsPost;
    use AdminSettingsExamples;
    use AdminAppearanceMenu;

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

        if (is_admin()) {
            add_action('admin_menu', array ($this, 'add_custom_options_page'));

            /**
             * Credentials
             */
            add_action('admin_init', array ($this, 'credentials_content'));

            /**
             * AdminGeneral
             */
            add_action('admin_init', array ($this, 'general_content'));

            /**
             * AdminSignup
             */
            add_action('admin_init', array ($this, 'signup_content'));

            /**
             * AdminLogin
             */
            add_action('admin_init', array ($this, 'login_content'));

            /**
             * AdminPost
             */
            add_action('admin_init', array ($this, 'post_content'));

            /**
             * AdminWoocommercePlugin
             */
            add_action('admin_init', array ($this, 'woocommerce_content'));

            /**
             * AdminExamples
             */
            add_action('admin_init', array ($this, 'examples_content'));

            /**
             * Admin menu in appearance menus
             */
            add_action('load-nav-menus.php', array ($this, 'add_nav_menu_meta_box'));

            /**
             * Load methods
             */
            $this->load_methods();
        } else {
            /**
             * Growtype form menu links update
             */
            add_filter('walker_nav_menu_start_el', array ($this, 'update_growtype_form_frontend_menu_links'), 10, 4);
        }

        /**
         * Submission
         */
        require_once GROWTYPE_FORM_PATH . 'admin/methods/submissions/class-growtype-form-submissions.php';
        new Growtype_Form_Submissions();

        /**
         * Login Enqueue styles
         */
        add_action('login_enqueue_scripts', array ($this, 'login_enqueue_scripts_callback'));
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
        wp_enqueue_style($this->Growtype_Form, plugin_dir_url(__FILE__) . 'css/growtype-form-admin.css', array (), GROWTYPE_FORM_VERSION, 'all');
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
    function add_custom_options_page()
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

    /**
     * @param $current
     * @return void
     */
    function growtype_form_settings_tabs($current = self::GROWTYPE_FORM_SETTINGS_DEFAULT_TAB)
    {
        $tabs['credentials'] = 'Credentials';
        $tabs['login'] = 'Login';
        $tabs['signup'] = 'Signup';
        $tabs['post'] = 'Post';

        if (class_exists('woocommerce')) {
            $tabs['woocommerce'] = 'Woocommerce';
        }

        $tabs['examples'] = 'Examples';

        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=growtype-form-settings&tab=$tab'>$name</a>";

        }
        echo '</h2>';
    }

    /**
     * @return void
     */
    function growtype_form_settings_form()
    {
        if (isset($_GET['page']) && $_GET['page'] == 'growtype-form-settings') {
            ?>

            <div class="wrap">

                <h1>Growtype Form - Settings</h1>

                <?php
                if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
                    echo '<div class="updated" ><p>Settings updated.</p></div>';
                }

                if (isset ($_GET['tab'])) {
                    $this->growtype_form_settings_tabs($_GET['tab']);
                } else {
                    $this->growtype_form_settings_tabs();
                }
                ?>

                <form id="growtype_form_main_settings_form" method="post" action="options.php">
                    <?php

                    if (isset ($_GET['tab'])) {
                        $tab = $_GET['tab'];
                    } else {
                        $tab = self::GROWTYPE_FORM_SETTINGS_DEFAULT_TAB;
                    }

                    switch ($tab) {
                        case 'credentials':
                            settings_fields('growtype_form_settings_credentials');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_credentials');
                            echo '</table>';

                            break;
                        case 'general':
                            settings_fields('growtype_form_settings_general');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_general');
                            echo '</table>';

                            break;
                        case 'login':
                            settings_fields('growtype_form_settings_login');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_login');
                            echo '</table>';

                            break;
                        case 'signup':
                            settings_fields('growtype_form_settings_signup');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_signup');
                            echo '</table>';

                            break;
                        case 'woocommerce' :
                            settings_fields('growtype_form_settings_woocommerce');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_woocommerce');
                            echo '</table>';

                            break;
                        case 'post' :
                            settings_fields('growtype_form_settings_post');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_post');
                            echo '</table>';

                            echo '<h2 class="title">Saving settings</h2>';
                            echo '<p>New post saving settings</p>';

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_post_saving');
                            echo '</table>';

                            echo '<h2 class="title">Email settings</h2>';
                            echo '<p>Email is sent when the new post is submitted</p>';

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_post_email');
                            echo '</table>';

                            break;
                        case 'examples' :
                            settings_fields('growtype_form_settings_examples');

                            echo '</br>';
                            echo '<b>Shortcode:</b> [growtype_form name="signup"] ' . "</br>";
                            echo '<b>Allowed input types:</b> ' . implode(',', Growtype_Form_General::GROWTYPE_FORM_ALLOWED_FIELD_TYPES);

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_examples');
                            echo '</table>';

                            break;
                    }

                    if ($tab !== 'examples') {
                        submit_button();
                    }

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
                            if (growtype_form_json_content.length > 0) {
                                growtype_form_json_content.editor.ace.setValue(JSON.stringify(JSON.parse($(value).text()), null, '\t'));
                            }
                        }
                    });

                    if ($('body').hasClass('settings_page_growtype-form-settings')) {
                        $('#growtype_form_main_settings_form input[type="submit"]').click(function () {
                            let forms = $(this).closest('form').find('.growtype_form_json_content');
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
                    }
                </script>
            </div>

            <?php
        }
    }

    /**
     * Load the required methods for this plugin.
     *
     */
    private function load_methods()
    {
        /**
         * Load users
         */
        if (!class_exists('WP_List_Table')) {
            require(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        require_once(ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php');

        /**
         * Accesses
         */
        require_once GROWTYPE_FORM_PATH . 'admin/methods/users/class-growtype-form-user-accesses.php';
        new Growtype_Form_User_Accesses();

        /**
         * Signup details
         */
        require_once GROWTYPE_FORM_PATH . 'admin/methods/users/class-growtype-form-signup-details.php';
        require_once GROWTYPE_FORM_PATH . 'admin/methods/users/class-growtype-form-signups-list-table.php';
        new Growtype_Form_Signup_Details();
    }
}
