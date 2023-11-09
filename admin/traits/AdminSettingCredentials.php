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

trait AdminSettingCredentials
{
    public function credentials_content()
    {
        /**
         * client_id
         */
        register_setting(
            'growtype_form_settings_credentials', // settings group name
            'growtype_form_settings_credentials_google_client_id', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_settings_credentials_google_client_id',
            'Google client id',
            array ($this, 'growtype_form_settings_credentials_google_client_id_callback'),
            'growtype-form-settings',
            'growtype_form_settings_credentials'
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
            'growtype-form-settings',
            'growtype_form_settings_credentials'
        );
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


