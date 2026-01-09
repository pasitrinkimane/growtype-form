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
            return [
                'success' => false,
                'message' => 'Class Growtype_Wc_Product not found'
            ];
        }

        /**
         * Validate ownership (update only)
         */
        if (!empty($product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR])) {
            if (!Growtype_Wc_Product::user_has_created_product(
                $product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR]
            )) {
                return [
                    'success' => false,
                    'message' => 'User has not created product'
                ];
            }
        }

        /**
         * Prices
         */
        $price_per_unit_buy_now = $product_data['data']['_price_per_unit_buy_now'] ?? null;
        $amount_in_units = $product_data['data']['_amount_in_units'] ?? null;
        $price = $product_data['data']['price'] ?? null;
        $regular_price = $product_data['data']['regular_price'] ?? null;

        if (!empty($price_per_unit_buy_now) && !empty($amount_in_units)) {
            $regular_price = $price_per_unit_buy_now * $amount_in_units;
        }

        /**
         * Meta fields
         */
        $meta_details = [];
        foreach ($this->get_product_meta_keys(growtype_form_default_product_type()) as $meta_key) {
            $meta_details[$meta_key] = apply_filters(
                'growtype_form_wc_crud_update_meta_data',
                $product_data['data'][$meta_key] ?? null,
                $meta_key,
                $product_data
            );
        }

        /**
         * Upload new gallery images and create a mapping
         */
        $uploaded_gallery_map = []; // Map by file name and size for matching

        if (!empty($product_data['files']['gallery']['name'])) {
            foreach ($product_data['files']['gallery']['name'] as $i => $name) {
                if (empty($name)) {
                    continue;
                }

                $file = [];
                foreach ($product_data['files']['gallery'] as $key => $values) {
                    $file[$key] = $values[$i];
                }

                $upload = self::upload_file_to_media_library($file);

                if (!empty($upload['attachment_id'])) {
                    $attachment_id = (int)$upload['attachment_id'];
                    
                    // Add meta tag to mark this as a product gallery image
                    update_post_meta($attachment_id, '_growtype_form_product_gallery', 'yes');
                    
                    // Create a key based on file name and size for matching with order data
                    $key = $name . '_' . $file['size'];
                    $uploaded_gallery_map[$key] = $attachment_id;
                }
            }
        }

        /**
         * Parse gallery order from frontend
         */
        $gallery_order = [];
        if (!empty($product_data['data']['gallery_order'])) {
            // The JSON comes with escaped quotes, so we need to stripslashes first
            $gallery_order_json = stripslashes($product_data['data']['gallery_order']);
            $gallery_order = json_decode($gallery_order_json, true);
        }

        /**
         * Build ordered gallery array based on frontend order
         */
        $all_image_ids = [];
        
        if (!empty($gallery_order) && is_array($gallery_order)) {
            foreach ($gallery_order as $item) {
                if ($item['type'] === 'preloaded' && !empty($item['id'])) {
                    // Add preloaded image ID
                    $all_image_ids[] = (int)$item['id'];
                } elseif ($item['type'] === 'new' && !empty($item['name'])) {
                    // Match newly uploaded image by name and size
                    $key = $item['name'] . '_' . $item['size'];
                    if (isset($uploaded_gallery_map[$key])) {
                        $all_image_ids[] = $uploaded_gallery_map[$key];
                    }
                }
            }
            
            /**
             * Delete images that were removed from gallery
             * Get the actual current gallery from database, not from form
             */
            if (!empty($product_data['data']['growtype_form_post_id'])) {
                $product_id_for_check = $product_data['data']['growtype_form_post_id'];
                
                // Get current gallery from database
                $current_gallery_string = get_post_meta($product_id_for_check, '_product_image_gallery', true);
                $current_gallery_ids = !empty($current_gallery_string) 
                    ? array_map('intval', explode(',', $current_gallery_string))
                    : [];
                
                // Also include current featured image
                $current_featured_id = get_post_thumbnail_id($product_id_for_check);
                if ($current_featured_id) {
                    $current_gallery_ids[] = (int)$current_featured_id;
                }
                
                // Find images that were in current gallery but not in new gallery
                $removed_image_ids = array_diff($current_gallery_ids, $all_image_ids);
                
                if (!empty($removed_image_ids)) {
                    foreach ($removed_image_ids as $removed_id) {
                        // Delete the attachment from WordPress
                        wp_delete_attachment($removed_id, true);
                    }
                }
            }
        } else {
            // Fallback: if no order data, merge preloaded and uploaded
            $preloaded_ids = array_map(
                'intval',
                $product_data['data']['image_uploader_old_gallery'] ?? []
            );
            $all_image_ids = array_values(array_unique(array_merge(
                $preloaded_ids,
                array_values($uploaded_gallery_map)
            )));
        }

        /**
         * Featured image logic
         */
        $featured_image_id = null;

        if (!empty($product_data['files']['featured_image'])) {
            $upload = self::upload_file_to_media_library(
                $product_data['files']['featured_image']
            );
            $featured_image_id = $upload['attachment_id'] ?? null;
        }

        /**
         * Downloadable files
         */
        $downloadable_files = [];
        foreach ($product_data['files']['downloadable_files'] ?? [] as $file) {
            $upload = self::upload_file_to_media_library($file);
            if ($upload) {
                $downloadable_files[] = $upload;
            }
        }

        /**
         * Extra meta
         */
        if (!empty($product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID])) {
            $meta_details['_product_creator_id'] =
                $product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID];
        }

        if (!empty($product_data['data']['extra_details'])) {
            $meta_details['_extra_details'] =
                implode(',', (array)$product_data['data']['extra_details']);
        }

        $meta_details['_img_placeholder_enabled'] = true;

        /**
         * Create or update product
         */
        $product = (new Growtype_Wc_Product)->create([
            'post_id' => $product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR] ?? null,
            'product_type' => growtype_form_default_product_type(),
            'post_title' => $product_data['data']['title'] ?? __('New product', 'growtype-form'),
            'post_status' => $product_data['data']['status'] ?? growtype_form_default_product_status(),
            'categories' => $product_data['data']['categories'] ?? null,
            'tags' => $product_data['data']['tags'] ?? '',
            'catalog_visibility' => growtype_form_default_product_catalog_visibility(),
            'short_description' => $product_data['data']['short_description'] ?? '',
            'description' => $product_data['data']['description'] ?? '',
            'downloadable_files' => $downloadable_files,
            'meta_details' => $meta_details,
            'regular_price' => $regular_price,
            'price' => $price,
        ]);

        $product_id = $product->get_id();

        /**
         * Only update gallery if there's order data (meaning user interacted with gallery)
         */
        if (!empty($gallery_order) && is_array($gallery_order) && count($all_image_ids) > 0) {
            
            // Always use first image from ordered gallery as featured image when order is changed
            $featured_image_id = $all_image_ids[0] ?? null;

            /**
             * Remove featured image from gallery
             */
            $gallery_ids = array_values(array_diff($all_image_ids, [$featured_image_id]));

            /**
             * Set featured image
             */
            if ($featured_image_id) {
                set_post_thumbnail($product_id, $featured_image_id);
            }

            /**
             * Set gallery only if we have images
             */
            if (!empty($gallery_ids)) {
                update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
            } elseif (isset($gallery_ids)) {
                // Clear gallery if explicitly empty
                delete_post_meta($product_id, '_product_image_gallery');
            }
        }

        /**
         * Filters
         */
        $product = apply_filters(
            'growtype_form_wc_crud_product_after_save',
            $product,
            $product_data
        );

        return [
            'success' => true,
            'product_id' => $product->get_id(),
            'message' => __('Product saved.', 'growtype-form')
        ];
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
    public static function user_has_uploaded_product($product_id, $user_id = null)
    {
        $user_id = $user_id ?? get_current_user_id();

        if (empty($user_id)) {
            return false;
        }

        $creator_id = (int)get_post_meta($product_id, '_product_creator_id', true);

        return $creator_id === $user_id;
    }
}
