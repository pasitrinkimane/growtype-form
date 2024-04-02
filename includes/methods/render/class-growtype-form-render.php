<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Render
{
    public function __construct()
    {
        $this->load_methods();
    }

    public function load_methods()
    {
        /**
         * General form
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/class-growtype-form-general.php';
        new Growtype_Form_General();

        /**
         * Authorisation form
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/class-growtype-form-auth.php';
        new Growtype_Form_Auth();

        /**
         * Newsletter form
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/class-growtype-form-newsletter.php';
        new Growtype_Form_Newsletter();
    }
}
