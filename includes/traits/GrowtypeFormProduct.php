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
 * Comes with file
 */
trait GrowtypeFormProduct
{
    /**
     * @return void
     */
    public function get_product_meta_keys($product_type = null)
    {
        $extra_meta_keys = [
            '_product_volume',
            '_amount_in_cases',
            '_cases_per_pallet',
            '_units_per_case',
            '_amount_in_units',
            '_product_location_city',
            '_product_location_country',
            '_price_per_unit'
        ];

        $extra_meta_keys = apply_filters('growtype_form_wc_crud_product_extra_meta_keys', $extra_meta_keys);

        return $extra_meta_keys;
    }

    /**
     * @param $data
     * @return array
     */
    public function create_or_update_product($product_data)
    {
        if (!class_exists('Growtype_Wc_Product')) {
            die('Class Growtype_Wc_Product not found');
        }

        /**
         * Price per unit buy now
         */
        $price_per_unit_buy_now = isset($product_data['data']['_price_per_unit_buy_now']) ? $product_data['data']['_price_per_unit_buy_now'] : '';

        /**
         * Amount in units
         */
        $amount_in_units = isset($product_data['data']['_amount_in_units']) ? $product_data['data']['_amount_in_units'] : null;

        /**
         * Price
         */
        $price = isset($product_data['data']['price']) ? $product_data['data']['price'] : '';

        /**
         * Regular price
         */
        $regular_price = isset($product_data['data']['regular_price']) ? $product_data['data']['regular_price'] : '';

        if (!empty($price_per_unit_buy_now) && !empty($amount_in_units)) {
            $regular_price = $price_per_unit_buy_now * $amount_in_units;
        }

        /**
         * Meta keys to update
         */
        $meta_details = [];
        $meta_keys_to_update = $this->get_product_meta_keys(growtype_form_default_product_type());

        foreach ($meta_keys_to_update as $meta_key) {
            $meta_value = isset($product_data['data'][$meta_key]) ? $product_data['data'][$meta_key] : null;

            $meta_value = apply_filters('growtype_form_wc_crud_update_meta_data', $meta_value, $meta_key, $product_data);

            $meta_details[$meta_key] = $meta_value;
        }

        /**
         * Get gallery ids
         */
        $gallery_data = $product_data['files']['gallery'] ?? null;
        $gallery_ids = [];

        if (!empty($gallery_data)) {

            $filter_files = array_filter($gallery_data['name'], function ($value) {
                return !empty($value);
            });

            $files_amount = is_array($filter_files) ? count($filter_files) : null;

            if (!empty($files_amount)) {
                $files_data = [];
                for ($index = 0; $index < $files_amount; $index++) {
                    foreach ($gallery_data as $key => $file) {
                        $files_data[$index][$key] = $file[$index];
                    }

                    $uploaded_attachment = self::upload_file_to_media_library($files_data[$index]);

                    if (isset($uploaded_attachment['attachment_id'])) {
                        array_push($gallery_ids, $uploaded_attachment['attachment_id']);
                    }
                }
            }
        }

        $gallery_preloaded = isset($product_data['data']['preloaded']) ? $product_data['data']['preloaded'] : null;

        $all_ids = $gallery_ids;

        if (!empty($gallery_preloaded)) {
            $all_ids = array_merge($gallery_ids, $gallery_preloaded);
        }

        $gallery_ids = $all_ids;

        /**
         * Update gallery ids
         */
        if (isset($all_ids)) {
            unset($all_ids[0]);
        }

        /**
         * Save featured image
         */
        $featured_image_data = $product_data['files']['featured_image'] ?? null;

        if (!empty($featured_image_data)) {
            $featured_image = self::upload_file_to_media_library($featured_image_data);
        }

        /**
         * Set featured image
         */
        $image_id = null;
        if (isset($featured_image) && !empty($featured_image)) {
            $image_id = $featured_image['attachment_id'];
        } elseif (isset($gallery_ids) && !empty($gallery_ids)) {
            $image_id = $gallery_ids[0];
        }

        /**
         * Save downloadable files
         */
        $downloadable_files_data = isset($product_data['files']['downloadable_files']) ? $product_data['files']['downloadable_files'] : null;

        $downloadable_files = [];
        if (!empty($downloadable_files_data)) {
            foreach ($downloadable_files_data as $downloadable_file_data) {
                $downloadable_file = self::upload_file_to_media_library($downloadable_file_data);

                if (!empty($downloadable_file)) {
                    array_push($downloadable_files, $downloadable_file);
                }
            }
        }

        /**
         * Add product creator id
         */
        $creator_id = isset($product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID]) ? $product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID] : null;

        if (!empty($creator_id)) {
            $meta_details['_product_creator_id'] = $creator_id;
        }

        /**
         * Add product
         */
        $extra_details = isset($product_data['data']['extra_details']) ? $product_data['data']['extra_details'] : null;

        if (!empty($extra_details)) {
            $meta_details['_extra_details'] = implode(',', $extra_details);
        }

        /**
         * Enable image placeholder
         */
        $meta_details['_img_placeholder_enabled'] = true;

        /**
         * Create product
         */
        $product_class = new Growtype_Wc_Product;

        $product_args = [
            'post_id' => isset($product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR]) ? $product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR] : null,
            'product_type' => growtype_form_default_product_type(),
            'post_title' => isset($product_data['data']['title']) ? $product_data['data']['title'] : __('New product', 'growtype-form'),
            'post_status' => isset($product_data['data']['status']) ? $product_data['data']['status'] : growtype_form_default_product_status(),
            'categories' => isset($product_data['data']['categories']) ? $product_data['data']['categories'] : null,
            'tags' => isset($product_data['data']['tags']) ? $product_data['data']['tags'] : '',
            'catalog_visibility' => growtype_form_default_product_catalog_visibility(),
            'short_description' => isset($product_data['data']['short_description']) ? $product_data['data']['short_description'] : '',
            'description' => isset($product_data['data']['description']) ? $product_data['data']['description'] : '',
            'gallery_image_ids' => $all_ids,
            'image_id' => $image_id,
            'downloadable_files' => $downloadable_files,
            'meta_details' => $meta_details,
            'regular_price' => !empty($regular_price) ? $regular_price : null,
            'price' => !empty($price) ? $price : null,
        ];

        $product = $product_class->create($product_args);

        /**
         * Apply external changes
         */
        $product = apply_filters('growtype_form_wc_crud_product_after_save', $product, $product_data);

        /**
         * Response
         */
        if ($product->get_id() === 0) {
            $response['success'] = false;
            $response['message'] = __("Something went wrong. Please contact administrator.", "growtype-form");
        } else {
            $response['product_id'] = $product->get_id();

            if (!empty($existing_product)) {
                $response['message'] = __("Product updated.", "growtype-form");
            } else {
                $response['message'] = __("Product created.", "growtype-form");
            }

            $response['success'] = true;
        }

        return $response;
    }

    /**
     * @param $terms
     * @param $taxonomy
     * @return array
     */
    public function get_terms_ids($terms, $taxonomy)
    {
        if (empty($terms)) {
            return null;
        }

        if (!empty($terms) && !is_array($terms)) {
            $terms = [$terms];
        }

        $terms_ids = [];

        foreach ($terms as $term) {

            if (empty($term)) {
                continue;
            }

            if (!term_exists($term, $taxonomy)) {
                $term_data = wp_insert_term($term, $taxonomy);
                $term_id = $term_data['term_id'];
                $term_taxonomy_id = $term_data['term_taxonomy_id'];
            } else {
                $term_data = get_terms($taxonomy, array ('hide_empty' => false, 'search' => $term));
                $term_data = $term_data[0] ?? null;
                $term_id = $term_data->term_id;
                $term_data_parent_id = $term_data->parent ?? null;

                if (isset($term_data_parent_id) && !empty($term_data_parent_id)) {
                    array_push($terms_ids, $term_data_parent_id);
                }
            }

            array_push($terms_ids, $term_id);
        }

        return $terms_ids;
    }

    /**
     * @param $product_id
     * @param $user_id
     * @return bool
     */
    public function user_has_uploaded_product($product_id, $user_id = null)
    {
        $user_id = $user_id ?? wp_get_current_user()->ID ?? null;

        if (empty($user_id)) {
            return false;
        }

        $creator_id = (int)get_post_meta($product_id, '_product_creator_id', true);

        return $creator_id === $user_id;
    }
}
