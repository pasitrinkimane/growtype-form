<?php

class Growtype_Form_Admin_Settings_Login
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_form_admin_settings_tabs', array ($this, 'settings_tab'), 30);
    }

    function settings_tab($tabs)
    {
        $tabs['login'] = 'Login';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * growtype_form_json_content
         */
        register_setting(
            'growtype_form_settings_login', // settings group name
            'growtype_form_login_json_content' // option name
        );

        add_settings_field(
            'growtype_form_login_json_content',
            'Json Content',
            array ($this, 'growtype_form_login_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_login'
        );

        /**
         * Login page
         */
        register_setting(
            'growtype_form_settings_login',
            'growtype_form_login_page'
        );

        add_settings_field(
            'growtype_form_login_page',
            'Login Page',
            array ($this, 'growtype_form_login_page_callback'),
            'growtype-form-settings',
            'growtype_form_settings_login'
        );

        /**
         * Login page template
         */
        register_setting(
            'growtype_form_settings_login', // settings group name
            'growtype_form_login_page_template', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_login_page_template',
            'Template',
            array ($this, 'growtype_form_login_page_template_callback'),
            'growtype-form-settings',
            'growtype_form_settings_login'
        );

        /**
         * Redirect after login
         */
        register_setting(
            'growtype_form_settings_login', // settings group name
            'growtype_form_redirect_after_login_page', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_redirect_after_login_page',
            'Redirect After Login To',
            array ($this, 'growtype_form_redirect_after_login_page_callback'),
            'growtype-form-settings',
            'growtype_form_settings_login'
        );

        /**
         * Show footer
         */
        register_setting(
            'growtype_form_settings_login', // settings group name
            'growtype_form_login_show_footer', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_login_show_footer',
            'Show Footer',
            array ($this, 'growtype_form_login_show_footer_callback'),
            'growtype-form-settings',
            'growtype_form_settings_login'
        );
    }

    /**
     * General form
     */
    function growtype_form_login_json_content_callback()
    {
        $json_content = get_option('growtype_form_login_json_content');

        if (empty($json_content)) {
            $context_options = array (
                "ssl" => array (
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );
            $json_content = file_get_contents(GROWTYPE_FORM_URL . 'admin/examples/login.json', false, stream_context_create($context_options));
        }
        ?>
        <textarea id="growtype_form_login_json_content" class="growtype_form_json_content" name="growtype_form_login_json_content" rows="40" cols="100" style="width: 100%;"><?= $json_content ?></textarea>
        <?php
    }

    /**
     * Login page
     */
    function growtype_form_login_page_callback()
    {
        $selected = growtype_form_login_page_ID();
        $pages = get_pages();
        ?>
        <select name='growtype_form_login_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>None</option>
            <option value='default' <?php selected($selected, 'default'); ?>>Default - Growtype form</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-form") ?> - Page</option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Login page template
     */
    function growtype_form_login_page_template_callback()
    {
        $selected = growtype_form_get_login_page_template();
        $options = ['template-default', 'template-wide', 'template-2'];
        ?>
        <select name='growtype_form_login_page_template'>
            <?php
            foreach ($options as $option) { ?>
                <option value='<?= $option ?>' <?php selected($selected, $option); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Redirect after login page
     */
    function growtype_form_redirect_after_login_page_callback()
    {
        $selected = growtype_form_default_redirect_after_login_page();
        $pages = get_pages();
        ?>
        <select name='growtype_form_redirect_after_login_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>None</option>
            <option value='default-profile' <?php selected($selected, 'default-profile'); ?>>Default profile page - Growtype Form</option>
            <option value='dashboard' <?php selected($selected, 'dashboard'); ?>>Dashboard</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-form") ?> - Page</option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Login show footer
     */
    function growtype_form_login_show_footer_callback()
    {
        $enabled = get_option('growtype_form_login_show_footer');
        ?>
        <input type="checkbox" name="growtype_form_login_show_footer" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }
}
