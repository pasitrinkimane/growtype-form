<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/partials
 */

trait AdminSettingsLogin
{
    public function login_content()
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
            'Login Form Template',
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
        ?>
        <textarea id="growtype_form_login_json_content" class="growtype_form_json_content" name="growtype_form_login_json_content" rows="40" cols="100" style="width: 100%;"><?= get_option('growtype_form_login_json_content') ?></textarea>
        <?php
    }

    /**
     * Login page
     */
    function growtype_form_login_page_callback()
    {
        $selected = get_option('growtype_form_login_page');
        $pages = get_pages();
        ?>
        <select name='growtype_form_login_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-form") ?></option>
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
        $options = ['template-default', 'template-1', 'template-2'];
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
        $selected = get_option('growtype_form_redirect_after_login_page');
        $pages = get_pages();
        ?>
        <select name='growtype_form_redirect_after_login_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <option value='dashboard' <?php selected($selected, 'dashboard'); ?>>Dashboard</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-form") ?></option>
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


