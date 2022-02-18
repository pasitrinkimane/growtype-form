<?php

/**
 * Class Growtype_Form_Wc_Crud
 * Woocommerce crud
 */
class Growtype_Form_Wc_Crud
{
    use Product;

    /**
     * @param $data
     * @return array
     */
    function create_or_update_product($product_data, $existing_product = null)
    {
        $product_title = $product_data['data']['title'] ?? __('New product', 'growtype-form');

        /**
         * Get wordpress crud methods
         */
        require_once GROWTYPE_FORM_PATH . 'includes/methods/crud/class-growtype-form-wp-crud.php';

        $wp_crud = new Growtype_Form_Wp_Crud();

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
         * Price
         */
        $price = $product_data['data']['price'] ?? '';

        /**
         * Regular price
         */
        $regular_price = $product_data['data']['regular_price'] ?? '';

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
         * Create product
         */
        if (!empty($existing_product)) {
            $product = $existing_product;
        } else {
            $product = new WC_Product_Simple();
            if (growtype_form_default_product_type() === 'grouped') {
                $product = new WC_Product_Grouped();
            } elseif (growtype_form_default_product_type() === 'external') {
                $product = new WC_Product_External();
            } elseif (growtype_form_default_product_type() === 'variable') {
                $product = new WC_Product_Variable();
            } elseif (class_exists('WC_Product_Auction') && growtype_form_default_product_type() === 'auction') {
                $product = new WC_Product_Auction($product);
            }
        }

        $product->set_name($product_title);
        $product->set_status($status);
        $product->set_catalog_visibility($visibility);
        $product->set_price($price);
        $product->set_sold_individually(true);

        /**
         * Set regular price
         */
        if (isset($regular_price)) {
            $product->set_regular_price($regular_price);
        }

        /**
         * Meta keys to update
         */
        $meta_keys_to_update = $this->get_product_extra_meta_keys();

        foreach ($meta_keys_to_update as $meta_key) {
            if (isset($product_data['data'][$meta_key])) {
                $meta_data = $product_data['data'][$meta_key];

                if ($meta_key === '_auction_dates_from' || $meta_key === '_auction_dates_to') {
                    $meta_data = date('Y-m-d H:i', strtotime($meta_data));
                }

                $product->update_meta_data($meta_key, $meta_data);
            }
        }

        /**
         * Auction start price
         */
        $price_per_unit = $product_data['data']['_price_per_unit'] ?? null;
        $amount_in_units = $product_data['data']['_amount_in_units'] ?? null;
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
                    $uploaded_attachment = $wp_crud->upload_file_to_media_library($files_data[$index]);
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
            $featured_image = $wp_crud->upload_file_to_media_library($featured_image_data);
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
            $downloadable_file = $wp_crud->upload_file_to_media_library($downloadable_file_data);
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
        $creator_id = $product_data['data'][Growtype_Form_Render::GROWTYPE_FORM_SUBMITTER_ID] ?? null;

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
                    $uploaded_attachment = $wp_crud->upload_file_to_media_library($files_data[$file_key]);
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
         * Set changes
         */
        $product = apply_filters('growtype_form_wc_crud_product_update', $product, $product_data);

        /**
         * Save product
         */
        $product->save();

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
    function get_terms_ids($terms, $taxonomy)
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
}
