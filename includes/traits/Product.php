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
trait Product
{
    /**
     * @return void
     */
    public function get_product_extra_meta_keys($product_type = null)
    {
        $extra_meta_keys = [
            '_regular_price',
            '_product_volume',
            '_amount_in_cases',
            '_cases_per_pallet',
            '_units_per_case',
            '_amount_in_units',
            '_product_location_city',
            '_product_location_country',
            '_price_per_unit',
            '_price_per_unit_buy_now',
        ];

        if ($product_type === 'auction') {
            $auction_meta_keys = [
                '_auction_dates_from',
                '_auction_dates_to',
                '_auction_bid_increment'
            ];

            $extra_meta_keys = array_merge($extra_meta_keys, $auction_meta_keys);
        }

        $external_extra_meta_keys = apply_filters('growtype_form_wc_crud_product_extra_meta_keys', '');

        if (!empty($external_extra_meta_keys)) {
            return array_merge($extra_meta_keys, $external_extra_meta_keys);
        }

        return $extra_meta_keys;
    }

    /**
     * @param $data
     * @return array
     */
    public function create_or_update_product($product_data)
    {
        /**
         * Product title
         */
        $product_title = $product_data['data']['title'] ?? __('New product', 'growtype-form');

        /**
         * Get categories
         */
        $categories = $product_data['data']['categories'] ?? null;

        if (!empty($categories)) {
            $category_ids = $this->get_terms_ids($categories, 'product_cat');
        }

        /**
         * Get tags
         */
        $tags = $product_data['data']['tags'] ?? null;

        if (!empty($tags)) {
            $tag_ids = $this->get_terms_ids($tags, 'product_tag');
        }

        /**
         * Status
         */
        $status = $product_data['data']['status'] ?? growtype_form_default_product_status();

        /**
         * Visibility
         */
        $visibility = growtype_form_default_product_catalog_visibility();

        /**
         * Price per unit
         */
        $price_per_unit = $product_data['data']['_price_per_unit'] ?? null;

        /**
         * Price per unit buy now
         */
        $price_per_unit_buy_now = $product_data['data']['_price_per_unit_buy_now'] ?? null;

        /**
         * Amount in units
         */
        $amount_in_units = $product_data['data']['_amount_in_units'] ?? null;

        /**
         * Price
         */
        $price = $product_data['data']['price'] ?? '';

        /**
         * Regular price
         */
        $regular_price = $product_data['data']['regular_price'] ?? '';

        if (!empty($price_per_unit_buy_now) && !empty($amount_in_units)) {
            $regular_price = $price_per_unit_buy_now * $amount_in_units;
        }

        /**
         * Get descriptions
         */
        $short_description = $product_data['data']['short_description'] ?? '';
        $description = $product_data['data']['description'] ?? '';

        /**
         * Auction start price
         */
        $auction_start_price = $product_data['data']['_auction_start_price'] ?? '';

        /**
         * Check if products exists
         */
        if (isset($product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR])) {
            $product = wc_get_product($product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_POST_IDENTIFICATOR]);

            if (!empty($product)) {
                wc_delete_product_transients($product->get_id());
            }

            if ($product && !$this->user_has_uploaded_product($product->get_id())) {
                $product = null;
            }
        }

        if (empty($product)) {
            $product = new WC_Product_Simple();
            if (growtype_form_default_product_type() === 'grouped') {
                $product = new WC_Product_Grouped();
            } elseif (growtype_form_default_product_type() === 'external') {
                $product = new WC_Product_External();
            } elseif (growtype_form_default_product_type() === 'variable') {
                $product = new WC_Product_Variable();
            } elseif (growtype_form_default_product_type() === 'auction') {
                $product = new WC_Product_Auction($product);
            }
        }

        $product->set_name($product_title);
        $product->set_status($status);
        $product->set_catalog_visibility($visibility);
        $product->set_sold_individually(true);

        /**
         * Meta keys to update
         */
        $meta_keys_to_update = $this->get_product_extra_meta_keys(growtype_form_default_product_type());

        foreach ($meta_keys_to_update as $meta_key) {
            if (isset($product_data['data'][$meta_key])) {
                $meta_data = $product_data['data'][$meta_key];

                if ($meta_key === '_auction_dates_from' || $meta_key === '_auction_dates_to') {
                    $meta_data = date('Y-m-d H:i', strtotime($meta_data));
                }

                if ($meta_key === '_auction_bid_increment') {
                    $meta_data = Growtype_Auction::BID_INCREMENT;
                }

                $product->update_meta_data($meta_key, $meta_data);
            }
        }

        /**
         * Auction set start price
         */
        $auction_start_price = isset($auction_start_price) && !empty($auction_start_price) ? $auction_start_price : $price_per_unit * $amount_in_units;

        if (!empty($auction_start_price)) {
            $product->update_meta_data('_auction_start_price', $auction_start_price);
        }

        /**
         * Save gallery
         */
        $gallery_data = $product_data['files']['gallery'] ?? null;
        $gallery_ids = [];

        if (!empty($gallery_data)) {

            $filter_files = array_filter($gallery_data['name'], function ($value) {
                return !empty($value);
            });

            $files_amount = count($filter_files) ?? null;

            if (!empty($files_amount)) {
                $files_data = [];
                for ($index = 0; $index < $files_amount; $index++) {
                    foreach ($gallery_data as $key => $file) {
                        $files_data[$index][$key] = $file[$index];
                    }
                    $uploaded_attachment = $this->upload_file_to_media_library($files_data[$index]);
                    array_push($gallery_ids, $uploaded_attachment['attachment_id']);
                }
            }
        }

        $gallery_preloaded = $product_data['data']['preloaded'] ?? null;

        $all_ids = $gallery_ids;

        if (!empty($gallery_preloaded)) {
            $all_ids = array_merge($gallery_ids, $gallery_preloaded);
        }

        $gallery_ids = $all_ids;

        /**
         * Set gallery ids
         */
        if (isset($all_ids)) {
            unset($all_ids[0]);
            $product->set_gallery_image_ids($all_ids);
        }

        /**
         * Save featured image
         */
        $featured_image_data = $product_data['files']['featured_image'] ?? null;

        if (!empty($featured_image_data)) {
            $featured_image = $this->upload_file_to_media_library($featured_image_data);
        }

        /**
         * Set featured image
         */
        if (isset($featured_image) && !empty($featured_image)) {
            $product->set_image_id($featured_image['attachment_id']);
        } elseif (isset($gallery_ids) && !empty($gallery_ids)) {
            $product->set_image_id($gallery_ids[0]);
        }

        /**
         * Set categories
         */
        if (!empty($category_ids)) {
            $product->set_category_ids($category_ids);
        }

        if (!empty($description)) {
            $product->set_description($description);
        }

        $product->set_short_description($short_description);

        if (!empty($tag_ids)) {
            $product->set_tag_ids($tag_ids);
        }

        /**
         * Save downloadable files
         */
        $downloadable_file_data = $product_data['files']['downloadable_file'] ?? null;

        if (!empty($downloadable_file_data)) {
            $downloadable_file = $this->upload_file_to_media_library($downloadable_file_data);
        }

        if (isset($downloadable_file) && !empty($downloadable_file)) {
            $product->set_virtual(true);
            $product->set_downloadable(true);

            $attachment_url = wp_get_attachment_url($downloadable_file['attachment_id']);
            $file_md5 = md5($attachment_url);

            $download = new WC_Product_Download();
            $download->set_name(get_the_title($downloadable_file['attachment_id']));
            $download->set_id($file_md5);
            $download->set_file($attachment_url);
            $downloads[$file_md5] = $download;

            $product->set_downloads($downloads);
        }

        /**
         * Src image
         */
        if (isset($featured_image) && !empty($featured_image)) {
            $src_img = wp_get_attachment_image_src($featured_image['attachment_id'], 'full');
        }

        /**
         * Add product creator id
         */
        $creator_id = $product_data['data'][Growtype_Form_Crud::GROWTYPE_FORM_SUBMITTER_ID] ?? null;

        if (!empty($creator_id)) {
            $product->update_meta_data('_product_creator_id', $creator_id);
        }

        /**
         * Add product
         */
        $extra_details = $product_data['data']['extra_details'] ?? null;

        if (!empty($extra_details)) {
            $product->update_meta_data('_extra_details', implode(',', $extra_details));
        }

        /**
         * Enable image placeholder
         */
        $product->update_meta_data('_img_placeholder_enabled', true);

        /**
         * Save shipping documents
         */
        $existing_shipping_documents = Growtype_Product::shipping_documents($product->get_id());
        $shipping_documents = $product_data['files']['shipping_documents'] ?? null;
        $shipping_documents_uploaded = [];

        if (!empty($shipping_documents)) {

            foreach ($existing_shipping_documents as $key => $document) {
                if (in_array($document['key'], array_keys($shipping_documents['name'])) && empty($shipping_documents['name'][$document['key']])) {
                    wp_delete_attachment($document['attachment_id']);
                    unset($existing_shipping_documents[$key]);
                }
            }

            $eisting_files = array_filter($shipping_documents['name'], function ($value) {
                return !empty($value);
            });

            $files_data = [];
            foreach ($eisting_files as $file_key => $file_name) {
                foreach ($shipping_documents as $key => $file) {
                    $files_data[$file_key][$key] = $file[$file_key];
                }

                if (!empty($files_data)) {
                    $uploaded_attachment = $this->upload_file_to_media_library($files_data[$file_key]);
                    $shipping_documents_uploaded[$file_key] = [
                        'key' => $file_key,
                        'attachment_id' => $uploaded_attachment['attachment_id'],
                        'url' => wp_get_attachment_url($uploaded_attachment['attachment_id']),
                        'name' => $file_name,
                    ];
                }
            }
        }

        $product_shipping_documents = array_merge($existing_shipping_documents, $shipping_documents_uploaded);

        /**
         * Shipping documents
         */
        if (isset($product_shipping_documents) && !empty($product_shipping_documents)) {
            $product->update_meta_data('_shipping_documents', array_values($product_shipping_documents));
        }

        /**
         * Auction bid increment
         */
        if ($product->is_type('auction')) {
            $auction_bid_increment = get_post_meta($product->get_id(), '_auction_bid_increment', true);

            if (empty($auction_bid_increment)) {
                $product->update_meta_data('_auction_bid_increment', Growtype_Auction::bid_increase_value());
            }
        }

        /**
         * Set regular price
         */
        if (!empty($regular_price)) {
            if ($product->is_type('auction')) {
                if (empty($product->get_id())) {
                    $product->update_meta_data('_regular_price', wc_format_decimal(wc_clean($regular_price)));
                } else {
                    update_post_meta($product->get_id(), '_regular_price', wc_format_decimal(wc_clean($regular_price)));
                }
            } else {
                $product->set_regular_price($regular_price);
            }
        }

        /**
         * Set price
         */
        if (empty($price)) {
            $price = $regular_price;
        }

        if (!empty($price)) {
            if (!$product->is_type('auction')) {
                $product->set_price($price);
            }
        }

        /**
         * Apply external changes
         */
        $product = apply_filters('growtype_form_wc_crud_product_update', $product, $product_data);

        /**
         * Save product
         */
        $product->save();

        /**
         * Update price for auctions only
         */
        if ($product->is_type('auction')) {
            update_post_meta($product->get_id(), '_price', $product->get_regular_price('edit'));
        }

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
