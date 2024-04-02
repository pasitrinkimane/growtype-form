<?php

class Growtype_Form_Admin_Settings_Woocommerce
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_form_admin_settings_tabs', array ($this, 'settings_tab'), 50);
    }

    function settings_tab($tabs)
    {
        if (class_exists('woocommerce')) {
            $tabs['woocommerce'] = 'Woocommerce';
        }

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * WooCommerce Product Upload Json Content
         */
        register_setting(
            'growtype_form_settings_woocommerce', // settings group name
            'growtype_form_wc_product_json_content' // option name
        );

        add_settings_field(
            'growtype_form_wc_product_json_content',
            'Json Content',
            array ($this, 'growtype_form_wc_product_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_woocommerce'
        );

        /**
         * Redirect after product creation
         */
        register_setting(
            'growtype_form_settings_woocommerce', // settings group name
            'growtype_form_redirect_after_product_creation', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_redirect_after_product_creation',
            'Redirect Url After Upload Form Submit',
            array ($this, 'growtype_form_redirect_after_product_creation_callback'),
            'growtype-form-settings',
            'growtype_form_settings_woocommerce'
        );

        /**
         * Product upload page
         */
        register_setting(
            'growtype_form_settings_woocommerce', // settings group name
            'growtype_form_product_upload_page'
        );

        add_settings_field(
            'growtype_form_product_upload_page',
            'Product Upload Page',
            array ($this, 'growtype_form_product_upload_page_callback'),
            'growtype-form-settings',
            'growtype_form_settings_woocommerce'
        );

        /**
         * Default product status
         */
        register_setting(
            'growtype_form_settings_woocommerce', // settings group name
            'growtype_form_default_product_status', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_default_product_status',
            'Default Product Status',
            array ($this, 'growtype_form_default_product_status_callback'),
            'growtype-form-settings',
            'growtype_form_settings_woocommerce'
        );

        /**
         * Default catalog visibility
         */
        register_setting(
            'growtype_form_settings_woocommerce', // settings group name
            'growtype_form_default_product_catalog_visibility', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_default_product_catalog_visibility',
            'Default Product Catalog Visibility',
            array ($this, 'growtype_form_default_product_catalog_visibility_callback'),
            'growtype-form-settings',
            'growtype_form_settings_woocommerce'
        );

        /**
         * Default product type
         */
        register_setting(
            'growtype_form_settings_woocommerce', // settings group name
            'growtype_form_default_product_type', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_default_product_type',
            'Default Product Type',
            array ($this, 'growtype_form_default_product_type_callback'),
            'growtype-form-settings',
            'growtype_form_settings_woocommerce'
        );
    }

    /**
     * Wc upload product
     */
    function growtype_form_wc_product_json_content_callback()
    {
        ?>
        <textarea id="growtype_form_wc_product_json_content" class="growtype_form_json_content" name="growtype_form_wc_product_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;"><?= get_option('growtype_form_wc_product_json_content') ?></textarea>
        <?php
    }

    /**
     * Wc upload product
     */
    function growtype_form_redirect_after_product_creation_callback()
    {
        $input_val = get_option('growtype_form_redirect_after_product_creation');
        ?>
        <input type="text" id="growtype_form_redirect_after_product_creation" class="regular-text qtranxs-translatable" name="growtype_form_redirect_after_product_creation" style="width: 100%;" value="<?= $input_val ?>">
        <?php
    }

    /**
     * Default product status
     */
    function growtype_form_default_product_status_callback()
    {
        $selected = growtype_form_default_product_status();
        $options = get_post_statuses();
        ?>
        <select name='growtype_form_default_product_status'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Catalog visibility
     */
    function growtype_form_default_product_catalog_visibility_callback()
    {
        $selected = growtype_form_default_product_catalog_visibility();
        $options = [];

        if (class_exists('woocommerce')) {
            $options = wc_get_product_visibility_options();
        }
        ?>
        <select name='growtype_form_default_product_catalog_visibility'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Catalog visibility
     */
    function growtype_form_default_product_type_callback()
    {
        $selected = growtype_form_default_product_type();
        $options = [];
        if (class_exists('woocommerce')) {
            $options = wc_get_product_types();
        }
        ?>
        <select name='growtype_form_default_product_type'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Upload page
     */
    function growtype_form_product_upload_page_callback()
    {
        $selected = get_option('growtype_form_product_upload_page');
        $pages = get_pages();
        ?>
        <select name='growtype_form_product_upload_page'>
            <option value='none' <?php selected($selected, 'none'); ?>>None</option>
            <?php
            foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected($selected, $page->ID); ?>><?= __($page->post_title, "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }
}
