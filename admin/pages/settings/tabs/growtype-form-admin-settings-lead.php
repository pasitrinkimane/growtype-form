<?php

class Growtype_Form_Admin_Settings_Lead
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_form_admin_settings_tabs', array ($this, 'settings_tab'), 50);
    }

    function settings_tab($tabs)
    {
        $tabs['lead'] = 'Lead';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * WooCommerce Product Upload Json Content
         */
//        register_setting(
//            'growtype_form_settings_lead', // settings group name
//            'growtype_form_newsletter_enabled' // option name
//        );
//
//        add_settings_field(
//            'growtype_form_newsletter_enabled',
//            'Enabled',
//            array ($this, 'growtype_form_newsletter_enabled_callback'),
//            'growtype-form-settings',
//            'growtype_form_settings_lead'
//        );
    }

    /**
     * Wc upload product
     */
    function growtype_form_newsletter_enabled_callback()
    {
        $enabled = get_option('growtype_form_newsletter_enabled');
        ?>
        <input type="checkbox" name="growtype_form_newsletter_enabled" value="1" <?php echo checked(1, $enabled, false) ?> />
        <?php
    }
}
