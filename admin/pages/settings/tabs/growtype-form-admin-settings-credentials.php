<?php

class Growtype_Form_Admin_Settings_Credentials
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_form_admin_settings_tabs', array ($this, 'settings_tab'), 10);
    }

    function settings_tab($tabs)
    {
        $tabs['credentials'] = 'Credentials';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * client_id
         */
        register_setting(
            'growtype_form_settings_credentials', // settings group name
            'growtype_form_settings_credentials_google_client_id', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_section(
            'growtype_form_settings_credentials_id',
            'Google Credentials',
            array ($this, 'render_section_intro'),
            'growtype_form_settings_credentials_section'
        );

        add_settings_field(
            'growtype_form_settings_credentials_google_client_id',
            'Google client id',
            array ($this, 'growtype_form_settings_credentials_google_client_id_callback'),
            'growtype_form_settings_credentials_section',
            'growtype_form_settings_credentials_id'
        );

        /**
         * client_id
         */
        register_setting(
            'growtype_form_settings_credentials', // settings group name
            'growtype_form_settings_credentials_google_client_secret', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_settings_credentials_google_client_secret',
            'Google client secret',
            array ($this, 'growtype_form_settings_credentials_google_client_secret_callback'),
            'growtype_form_settings_credentials_section',
            'growtype_form_settings_credentials_id'
        );
    }

    /**
     * Render the qTranslate-X section.
     */
    function render_section_intro()
    {
        ?>
        Credentials will be used for Google auth.
        <?php
    }

    /**
     * client_id
     */
    function growtype_form_settings_credentials_google_client_id_callback()
    {
        $value = get_option('growtype_form_settings_credentials_google_client_id');
        ?>
        <input type="text" name="growtype_form_settings_credentials_google_client_id" class="regular-text" value="<?php echo $value ?>"/>
        <?php
    }

    /**
     * client_secret
     */
    function growtype_form_settings_credentials_google_client_secret_callback()
    {
        $value = get_option('growtype_form_settings_credentials_google_client_secret');
        ?>
        <input type="text" name="growtype_form_settings_credentials_google_client_secret" class="regular-text" value="<?php echo $value ?>"/>
        <?php
    }
}
