<?php

class Growtype_Form_Admin_Page
{
    public function __construct()
    {
        $this->load_pages();

        /**
         * Remove menu elements
         */
        add_action('admin_menu', array ($this, 'remove_menu_elements'));
    }

    function remove_menu_elements()
    {
        remove_submenu_page('edit.php?post_type=' . Growtype_Form_Admin_Form::POST_TYPE_NAME, 'post-new.php?post_type=' . Growtype_Form_Admin_Form::POST_TYPE_NAME);
    }

    public function load_pages()
    {
        /**
         * Forms
         */
        require_once GROWTYPE_FORM_PATH . 'admin/pages/forms/class-growtype-form-admin-form.php';
        new Growtype_Form_Admin_Form();

        /**
         * Submissions
         */
        require_once GROWTYPE_FORM_PATH . 'admin/pages/submissions/class-growtype-form-admin-submission.php';
        new Growtype_Form_Admin_Submission();

        /**
         * Leads
         */
        require_once GROWTYPE_FORM_PATH . 'admin/pages/leads/class-growtype-form-admin-lead.php';
        new Growtype_Form_Admin_Lead();

        /**
         * Settings
         */
        require_once GROWTYPE_FORM_PATH . 'admin/pages/settings/class-growtype-form-admin-settings.php';
        new Growtype_Form_Admin_Settings();

        /**
         * Settings
         */
        require_once GROWTYPE_FORM_PATH . 'admin/pages/users/class-growtype-form-admin-user.php';
        new Growtype_Form_Admin_User();

        /**
         * Media management
         */
        require GROWTYPE_FORM_PATH . '/admin/pages/media/class-growtype-form-admin-media.php';
        new Growtype_Form_Admin_Media();
    }
}
