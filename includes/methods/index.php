<?php

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

/**
 * Render
 */
require_once GROWTYPE_FORM_PATH . 'includes/methods/render/class-growtype-form-render.php';
$this->loader = new Growtype_Form_Render();

/**
 * Plugins
 */
require_once GROWTYPE_FORM_PATH . 'includes/methods/plugins/index.php';
