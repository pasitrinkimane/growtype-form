<?php

/**
 *
 */
class Growtype_Form_Profile_Settings extends Growtype_Form_Profile
{
    use GrowtypeFormUser;

    const URL_PATH = Growtype_Form_Profile_Home::URL_PATH . '/settings';

    public function __construct()
    {
        add_filter('growtype_form_user_profile_custom_pages', function ($pages){
            $pages[] = [
                'handler' => 'Growtype_Form_Profile_Settings',
                'title' => 'Settings',
                'key' => 'profile-settings',
                'view_path' => 'profile.settings',
                'url' => self::URL_PATH
            ];

            return $pages;
        });
    }
}
