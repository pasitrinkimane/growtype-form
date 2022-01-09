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

trait AdminSettingsSignup
{
    /**
     * @return void
     */
    public function signup_content()
    {
        /**
         * growtype_form_json_content
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_signup_json_content' // option name
        );

        add_settings_field(
            'growtype_form_signup_json_content',
            'Json Content',
            array ($this, 'growtype_form_signup_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );

        /**
         * Register page
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_signup_page'
        );

        add_settings_field(
            'growtype_form_signup_page',
            'Signup Page',
            array ($this, 'growtype_form_signup_page_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );

        /**
         * Signup page template
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_signup_page_template', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_signup_page_template',
            'Signup Page Template',
            array ($this, 'growtype_form_signup_page_template_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );

        /**
         * Redirect after signup
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_redirect_after_signup_page', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_redirect_after_signup_page',
            'Redirect After Signup To',
            array ($this, 'growtype_form_redirect_after_signup_page_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );

        /**
         * Default user role
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_default_user_role', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_default_user_role',
            'Default User Role',
            array ($this, 'growtype_form_default_user_role_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );

        /**
         * Active user role
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_active_user_role', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_active_user_role',
            'Active User Role',
            array ($this, 'growtype_form_active_user_role_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );

        /**
         * Allow simple password
         */
        register_setting(
            'growtype_form_settings_signup', // settings group name
            'growtype_form_allow_simple_password', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_allow_simple_password',
            'Allow simple password',
            array ($this, 'growtype_form_allow_simple_password_callback'),
            'growtype-form-settings',
            'growtype_form_settings_signup'
        );
    }

    /**
     * General form
     */
    function growtype_form_signup_json_content_callback()
    {
        ?>
        <textarea id="growtype_form_signup_json_content" class="growtype_form_json_content" name="growtype_form_signup_json_content" rows="40" cols="100" style="width: 100%;"><?= get_option('growtype_form_signup_json_content') ?></textarea>
        <?php
    }

    /**
     * Register page
     */
    function growtype_form_signup_page_callback()
    {
        $selected = get_option('growtype_form_signup_page');
        $pages = get_pages();
        ?>
        <select name='growtype_form_signup_page'>
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
    function growtype_form_signup_page_template_callback()
    {
        $selected = growtype_form_get_signup_page_template();
        $options = ['template-default', 'template-1', 'template-2'];
        ?>
        <select name='growtype_form_signup_page_template'>
            <?php
            foreach ($options as $option) { ?>
                <option value='<?= $option ?>' <?php selected($selected, $option); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Register page
     */
    function growtype_form_redirect_after_signup_page_callback()
    {
        $selected = get_option('growtype_form_redirect_after_signup_page');
        $pages = get_pages();
        ?>
        <select name='growtype_form_redirect_after_signup_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>none</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * User role
     */
    function growtype_form_default_user_role_callback()
    {
        global $wp_roles;

        $selected = get_option('growtype_form_default_user_role');
        $roles = $wp_roles->roles;
        ?>
        <select name='growtype_form_default_user_role'>
            <?php
            foreach ($roles as $role => $role_details) { ?>
                <option value='<?= $role ?>' <?php selected($selected, $role); ?>><?= __($role_details['name'], "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * User role
     */
    function growtype_form_active_user_role_callback()
    {
        global $wp_roles;

        $selected = get_option('growtype_form_active_user_role');
        $roles = $wp_roles->roles;
        ?>
        <select name='growtype_form_active_user_role'>
            <?php
            foreach ($roles as $role => $role_details) { ?>
                <option value='<?= $role ?>' <?php selected($selected, $role); ?>><?= __($role_details['name'], "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Allow simple password for signup
     */
    function growtype_form_allow_simple_password_callback()
    {
        $enabled = get_option('growtype_form_allow_simple_password');
        ?>
        <input type="checkbox" name="growtype_form_allow_simple_password" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }

    /**
     * @param $user
     * @return array
     */
    public function growtype_form_get_user_signup_data($user)
    {
        $user_meta = get_user_meta($user->ID);
        $form_name = isset($user_meta['growtype_form_name']) ? $user_meta['growtype_form_name'][0] : null;
        $json_form_encoded = get_option('growtype_form_signup_json_content');
        $json_form = json_decode($json_form_encoded, true);
        $form_data = isset($json_form[$form_name]) ? $json_form[$form_name] : $json_form['signup'];
        $main_fields = $form_data['main_fields'];

        $user_data = [];
        foreach ($main_fields as $field) {
            $meta_value = isset($field['name']) && isset($user_meta[$field['name']]) ? $user_meta[$field['name']][0] : null;
            if (!empty($meta_value)) {
                $user_data[$field['name']] = [
                    'label' => $field['label'],
                    'value' => $meta_value
                ];
            }
        }

        return $user_data;
    }
}
