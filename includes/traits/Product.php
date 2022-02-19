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

trait Product
{
    /**
     * @return void
     */
    public function get_product_extra_meta_keys()
    {
        $extra_meta_keys = [
            '_regular_price',
            '_product_volume',
            '_amount_in_cases',
            '_cases_per_pallet',
            '_amount_in_units',
            '_product_location_city',
            '_product_location_country',
            '_price_per_unit',
            '_auction_dates_from',
            '_auction_dates_to'
        ];

        $external_extra_meta_keys = apply_filters('growtype_form_wc_crud_product_extra_meta_keys', '');

        if (!empty($external_extra_meta_keys)) {
            return array_merge($extra_meta_keys, $external_extra_meta_keys);
        }

        return $extra_meta_keys;
    }
}