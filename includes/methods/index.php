<?php

class Growtype_Form_Methods
{
    private $Growtype_Form_Crud;
    private $Growtype_Form_Login;
    private $Growtype_Form_Signup;
    private $loader;

    public function __construct()
    {
        $this->load_methods();
    }

    private function load_methods()
    {
        /**
         * CRUD
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/notice/Growtype_Form_Notice.php';
        $this->Growtype_Form_Crud = new Growtype_Form_Notice();

        /**
         * CRUD
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/crud/class-growtype-form-crud.php';
        $this->Growtype_Form_Crud = new Growtype_Form_Crud();

        /**
         * Login
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/login/class-growtype-form-login.php';
        $this->Growtype_Form_Login = new Growtype_Form_Login();

        /**
         * Signup
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/signup/class-growtype-form-signup.php';
        $this->Growtype_Form_Signup = new Growtype_Form_Signup();

        /**
         * User
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/user/index.php';
        new Growtype_Form_Methods_User();

        /**
         * Render
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/render/class-growtype-form-render.php';
        new Growtype_Form_Render();

        /**
         * Plugins
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/plugins/index.php';
    }
}
