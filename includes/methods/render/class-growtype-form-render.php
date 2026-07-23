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
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/general/index.php';
        new Growtype_Form_General();

        /**
         * Authorisation form
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/auth/index.php';
        new Growtype_Form_Auth();

        /**
         * Newsletter form
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/newsletter/index.php';
        new Growtype_Form_Newsletter();

        /**
         * Feedback form
         */
        include_once GROWTYPE_FORM_PATH . 'includes/methods/render/forms/feedback/index.php';
        new Growtype_Form_Feedback();
    }
}
