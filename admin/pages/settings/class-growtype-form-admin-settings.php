<?php

class Growtype_Form_Admin_Settings
{
    public function __construct()
    {
        $this->load_tabs();

        add_action('admin_menu', array ($this, 'admin_menu_pages'));

        add_action('admin_footer', array ($this, 'admin_footer_extend'));
    }

    /**
     * Register the options page with the Wordpress menu.
     */
    function admin_menu_pages()
    {
        add_options_page(
            'Growtype - Form',
            'Growtype - Form',
            'manage_options',
            Growtype_Form_Admin::SETTINGS_PAGE_SLUG,
            array ($this, 'options_page_content'),
            1
        );
    }

    function options_page_content()
    {
        if (isset($_GET['page']) && $_GET['page'] === Growtype_Form_Admin::SETTINGS_PAGE_SLUG) {

            if (isset ($_GET['tab'])) {
                $tab = $_GET['tab'];
            } else {
                $tab = Growtype_Form_Admin::SETTINGS_DEFAULT_TAB;
            }
            ?>

            <div class="wrap">

                <h1>Growtype Form - Settings</h1>

                <?php
                if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
                    echo '<div class="updated" ><p>Settings updated.</p></div>';
                }

                if (isset ($_GET['tab'])) {
                    $this->render_settings_tab_render($_GET['tab']);
                } else {
                    $this->render_settings_tab_render();
                }
                ?>

                <form id="growtype_form_settings_form" method="post" action="options.php">
                    <?php
                    switch ($tab) {
                        case 'general':
                            settings_fields('growtype_form_settings_general');

                            echo '<table class="form-table">';
                            do_settings_sections('growtype_form_settings_general_submissions_section');
                            echo '</table>';

                            break;
                        case 'credentials':
                            settings_fields('growtype_form_settings_credentials');

                            echo '<table class="form-table">';
                            do_settings_sections('growtype_form_settings_credentials_section');
                            echo '</table>';

                            break;
                        case 'login':
                            settings_fields('growtype_form_settings_login');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_login');
                            echo '</table>';

                            break;
                        case Growtype_Form_Admin_Settings_Signup::TAB_SLUG:
                            settings_fields('growtype_form_settings_signup');

                            echo '<table class="form-table">';
                            do_settings_sections('growtype_form_settings_signup_general_section');
                            do_settings_sections('growtype_form_settings_signup_email_confirmation_section');
                            do_settings_sections('growtype_form_settings_signup_users_section');
                            do_settings_sections('growtype_form_settings_signup_onboarding_section');
                            echo '</table>';

                            break;
                        case 'woocommerce' :
                            settings_fields('growtype_form_settings_woocommerce');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_woocommerce');
                            echo '</table>';

                            break;
                        case 'lead' :
                            settings_fields('growtype_form_settings_lead');

                            echo '<table class="form-table">';
                            do_settings_fields('growtype-form-settings', 'growtype_form_settings_lead');
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
                            echo '<b>Allowed input types:</b> ' . implode(',', Growtype_Form_General::ALLOWED_FIELD_TYPES);

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
            </div>

            <?php
        }
    }

    function admin_footer_extend()
    {
        if ((isset($_GET['page']) && $_GET['page'] === Growtype_Form_Admin::SETTINGS_PAGE_SLUG) && in_array($_GET['tab'] ?? '', ['examples', 'post', Growtype_Form_Admin_Settings_Signup::TAB_SLUG, 'login', 'woocommerce'])) {
            Growtype_Form_Admin::init_json_editor();
        }
    }

    function settings_tabs()
    {
        return apply_filters('growtype_form_admin_settings_tabs', []);
    }

    function render_settings_tab_render($current = Growtype_Form_Admin::SETTINGS_DEFAULT_TAB)
    {
        $tabs = $this->settings_tabs();

        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=" . Growtype_Form_Admin::SETTINGS_PAGE_SLUG . "&tab=$tab'>$name</a>";

        }
        echo '</h2>';
    }

    public function load_tabs()
    {
        /**
         * General
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-general.php';
        new Growtype_Form_Admin_Settings_General();

        /**
         * Login
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-login.php';
        new Growtype_Form_Admin_Settings_Login();

        /**
         * Signup
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-signup.php';
        new Growtype_Form_Admin_Settings_Signup();

        /**
         * Post
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-post.php';
        new Growtype_Form_Admin_Settings_Post();

        /**
         * Credentials
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-credentials.php';
        new Growtype_Form_Admin_Settings_Credentials();

        /**
         * Woocommerce
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-woocommerce.php';
        new Growtype_Form_Admin_Settings_Woocommerce();

        /**
         * Lead
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-lead.php';
        new Growtype_Form_Admin_Settings_Lead();

        /**
         * Examples
         */
        include_once GROWTYPE_FORM_PATH . 'admin/pages/settings/tabs/growtype-form-admin-settings-examples.php';
        new Growtype_Form_Admin_Settings_Examples();
    }
}
