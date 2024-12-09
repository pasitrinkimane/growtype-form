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
//        $tabs['credentials'] = 'Credentials';
        return $tabs;
    }

    function admin_settings()
    {
    }
}
