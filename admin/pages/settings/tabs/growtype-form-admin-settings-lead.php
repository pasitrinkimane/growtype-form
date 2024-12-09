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
//        $tabs['lead'] = 'Lead';
        return $tabs;
    }

    function admin_settings()
    {
    }
}
