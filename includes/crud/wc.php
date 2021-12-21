<?php

/**
 * Class Growtype_Form_Wc_Crud
 * Woocommerce crud
 */
class Growtype_Form_Wc_Crud
{
    /**
     * @param $data
     * @return array
     */
    function create_product($product_data)
    {
        $product_title = $product_data['data']['title'] ?? 'Demo';

        /**
         * Get wordpress crud methods
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'crud/wp.php';

        $wp_crud = new Growtype_Form_Wp_Crud();

        /**
         * Get categories
         */
        $categories = $product_data['data']['categories'] ?? null;
        $category_ids = $this->get_terms_ids($categories, 'product_cat');

        /**
         * Get tags
         */
        $tags = $product_data['data']['tags'] ?? null;
        $tag_ids = $this->get_terms_ids($tags, 'product_tag');

        /**
         * Status
         */
        $status = $product_data['data']['status'] ?? 'pending';

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
        $long_description = $product_data['data']['long_description'] ?? '';

        /**
         * Save files
         */
        $featured_image_data = $product_data['files']['featured_image'] ?? null;

        if (!empty($featured_image_data)) {
            $featured_image = $wp_crud->upload_file_to_media_library($featured_image_data);
        }

        $downloadable_file_data = $product_data['files']['downloadable_file'] ?? null;

        if (!empty($downloadable_file_data)) {
            $downloadable_file = $wp_crud->upload_file_to_media_library($downloadable_file_data);
        }

        /**
         * Create product
         */
        $product = new WC_Product_Simple();
        $product->set_name($product_title);
        $product->set_status($status);
        $product->set_catalog_visibility('visible');
        $product->set_price($price);
        $product->set_regular_price($regular_price);
        $product->set_sold_individually(true);

        /**
         * Set featured image
         */
        if (isset($featured_image) && !empty($featured_image)) {
            $product->set_image_id($featured_image['attachment_id']);
        }

        if (!empty($category_ids)) {
            $product->set_category_ids($category_ids);
        }

        if (!empty($long_description)) {
            $product->set_description($long_description);
        }

        $product->set_short_description($short_description);

        if (!empty($tag_ids)) {
            $product->set_tag_ids($tag_ids);
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
            $response['message'] = __("Product created.", "growtype-form");
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
