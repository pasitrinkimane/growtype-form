<?php

class Growtype_Form_Methods_User
{
    private $Growtype_Form_Profile;

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
        $this->Growtype_Form_Profile = new Growtype_Form_Profile();

        /**
         * Profile settings
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/partials/class-growtype-form-profile-settings.php';
        $this->Growtype_Form_Profile = new Growtype_Form_Profile_Settings();
    }
}
