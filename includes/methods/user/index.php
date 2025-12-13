<?php

class Growtype_Form_Methods_User
{
    public function __construct()
    {
        $this->load_methods();
    }

    private function load_methods()
    {
        /**
         * Profile
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile.php';
        new Growtype_Form_Profile();

        /**
         * Profile
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-home.php';
        new Growtype_Form_Profile_Home();

        /**
         * Profile settings
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-settings.php';
        new Growtype_Form_Profile_Settings();

        /**
         * Profile settings
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-details.php';
        new Growtype_Form_Profile_Details();

        /**
         * Profile settings
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-edit.php';
        new Growtype_Form_Profile_Edit();

        /**
         * Profile settings
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-roles.php';
        new Growtype_Form_Profile_Roles();

        /**
         * Profile settings
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-security.php';
        new Growtype_Form_Profile_Security();

        /**
         * Profile email
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-email.php';
        new Growtype_Form_Profile_Email();
    }
}
