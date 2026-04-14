<?php

use EmailValidator\EmailValidator;

/**
 *
 */
class Growtype_Form_Admin_Lead
{
    const POST_TYPE_NAME = 'gf_lead';

    protected $fetch;
    protected $export;
    protected $validate;

    public function __construct()
    {
        /**
         * Load extra methods first so $this->export etc. are ready for hooks below
         */
        $this->load_methods();
    }

    public function load_methods()
    {
        /**
         * UI
         */
        include_once 'partials/class-growtype-form-admin-lead-ui.php';
        new Growtype_Form_Admin_Lead_Ui();

        /**
         * Fetch
         */
        include_once 'partials/class-growtype-form-admin-lead-fetch.php';
        $this->fetch = new Growtype_Form_Admin_Lead_Fetch();

        /**
         * Validate
         */
        include_once 'partials/class-growtype-form-admin-lead-validate.php';
        $this->validate = new Growtype_Form_Admin_Lead_Validate();

        /**
         * Export
         */
        include_once 'partials/export/class-growtype-form-admin-lead-export.php';
        $this->export = new Growtype_Form_Admin_Lead_Export();

        /**
         * Post type
         */
        include_once 'partials/class-growtype-form-admin-lead-post-type.php';
        new Growtype_Form_Admin_Lead_Post_Type();

        /**
         * Meta boxes
         */
        include_once 'partials/class-growtype-form-admin-lead-meta-boxes.php';
        new Growtype_Form_Admin_Lead_Meta_Boxes();

        /**
         * Admin columns
         */
        include_once 'partials/class-growtype-form-admin-lead-columns.php';
        new Growtype_Form_Admin_Lead_Columns();

        /**
         * Filters
         */
        include_once 'partials/class-growtype-form-admin-lead-filters.php';
        new Growtype_Form_Admin_Lead_Filters();

        /**
         * Custom actions
         */
        include_once 'partials/class-growtype-form-admin-lead-custom-actions.php';
        new Growtype_Form_Admin_Lead_Custom_Actions();

        /**
         * Events
         */
        include_once 'partials/class-growtype-form-admin-lead-events.php';
        new Growtype_Form_Admin_Lead_Events();

        /**
         * Crud
         */
        include_once 'partials/class-growtype-form-admin-lead-crud.php';
        new Growtype_Form_Admin_Lead_Crud();
    }

    public static function insert($data)
    {
        include_once 'partials/class-growtype-form-admin-lead-crud.php';
        return Growtype_Form_Admin_Lead_Crud::insert($data);
    }

    public static function get_by_title($title)
    {
        include_once 'partials/class-growtype-form-admin-lead-crud.php';
        return Growtype_Form_Admin_Lead_Crud::get_by_title($title);
    }

    public static function get_all_by_title($title)
    {
        include_once 'partials/class-growtype-form-admin-lead-crud.php';
        return Growtype_Form_Admin_Lead_Crud::get_all_by_title($title);
    }

    public static function delete_lead($user_id)
    {
        include_once 'partials/class-growtype-form-admin-lead-crud.php';
        return Growtype_Form_Admin_Lead_Crud::delete_lead($user_id);
    }

    public static function update_events_log($id, $details)
    {
        include_once 'partials/class-growtype-form-admin-lead-events.php';
        return Growtype_Form_Admin_Lead_Events::update_events_log($id, $details);
    }

    public static function get_events_log($id)
    {
        include_once 'partials/class-growtype-form-admin-lead-events.php';
        return Growtype_Form_Admin_Lead_Events::get_events_log($id);
    }
}
