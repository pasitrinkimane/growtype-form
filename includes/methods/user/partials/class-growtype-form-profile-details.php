<?php

/**
 *
 */
class Growtype_Form_Profile_Details extends Growtype_Form_Profile
{
    use GrowtypeFormUser;

    const URL_PATH = Growtype_Form_Profile_Home::URL_PATH . '/details';

    public function __construct()
    {
        add_filter('growtype_form_user_profile_custom_pages', function ($pages) {
            $pages[] = [
                'handler' => 'Growtype_Form_Profile_Details',
                'title' => 'Profile Details',
                'key' => 'profile-details',
                'view_path' => 'profile.details',
                'url' => self::URL_PATH
            ];

            return $pages;
        });
    }
}
