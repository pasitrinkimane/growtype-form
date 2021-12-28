<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/partials
 */

trait AdminSettingsWoocommercePlugin
{
    public function woocommerce_content()
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
            'Redirect Url After Product Creation',
            array ($this, 'growtype_form_redirect_after_product_creation_callback'),
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
        <input id="growtype_form_redirect_after_product_creation" class="input" name="growtype_form_redirect_after_product_creation" style="width: 100%;" value="<?= $input_val ?>">
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
        $options = wc_get_product_visibility_options();
        ?>
        <select name='growtype_form_default_product_catalog_visibility'>
            <?php
            foreach ($options as $value => $option) { ?>
                <option value='<?= $value ?>' <?php selected($selected, $value); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }
}
