<?php

class Growtype_Form_Admin_Settings_General
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_form_admin_settings_tabs', array ($this, 'settings_tab'), 0);
    }

    function settings_tab($tabs)
    {
        $tabs['general'] = 'General';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * client_id
         */
        add_settings_section(
            'growtype_form_settings_general_submissions_section_id',
            'Submissions',
            function () {
                echo '<p>Form submissions settings</p>';
            },
            'growtype_form_settings_general_submissions_section'
        );

        /**
         * Enabled
         */
        register_setting(
            'growtype_form_settings_general', // settings group name
            'growtype_form_settings_submissions_enabled', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_settings_submissions_enabled',
            'Submissions enabled',
            array ($this, 'growtype_form_settings_submissions_enabled_callback'),
            'growtype_form_settings_general_submissions_section',
            'growtype_form_settings_general_submissions_section_id'
        );

        /**
         * Duplicates
         */
        register_setting(
            'growtype_form_settings_general', // settings group name
            'growtype_form_settings_prevent_duplicate_submissions', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_settings_prevent_duplicate_submissions',
            'Prevent duplicate submissions',
            array ($this, 'growtype_form_settings_prevent_duplicate_submissions_callback'),
            'growtype_form_settings_general_submissions_section',
            'growtype_form_settings_general_submissions_section_id'
        );
    }

    function growtype_form_settings_submissions_enabled_callback()
    {
        $enabled = get_option('growtype_form_settings_submissions_enabled');
        ?>
        <input type="checkbox" name="growtype_form_settings_submissions_enabled" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }

    function growtype_form_settings_prevent_duplicate_submissions_callback()
    {
        $enabled = get_option('growtype_form_settings_prevent_duplicate_submissions');
        ?>
        <input type="checkbox" name="growtype_form_settings_prevent_duplicate_submissions" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }
}
