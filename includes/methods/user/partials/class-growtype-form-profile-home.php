<?php

/**
 *
 */
class Growtype_Form_Profile_Home extends Growtype_Form_Profile
{
    use GrowtypeFormUser;

    const URL_PATH = 'userprofile';

    public function __construct()
    {
        add_filter('growtype_form_user_profile_custom_pages', function ($pages) {
            $pages[] = [
                'handler' => 'Growtype_Form_Profile_Home',
                'title' => 'Profile',
                'key' => 'profile',
                'view_path' => 'profile.index',
                'url' => self::URL_PATH
            ];

            return $pages;
        });
    }
}
