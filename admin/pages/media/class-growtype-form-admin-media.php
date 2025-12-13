<?php

/**
 * Media management for Growtype Form
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/pages/media
 */

class Growtype_Form_Admin_Media
{
    public function __construct()
    {
        /**
         * Filter product gallery images in media library
         */
        add_filter('ajax_query_attachments_args', array($this, 'filter_product_gallery_images_in_media_library'));
        add_action('pre_get_posts', array($this, 'filter_product_gallery_in_list_mode'));
        
        /**
         * Add Product Gallery submenu under Media
         */
        add_action('admin_menu', array($this, 'add_product_gallery_submenu'));
        
        /**
         * Fix submenu highlighting
         */
        add_filter('parent_file', array($this, 'fix_product_gallery_menu_highlight'));
        add_filter('submenu_file', array($this, 'fix_product_gallery_submenu_highlight'));
    }
    
    /**
     * Filter product gallery images in media library based on current page
     * 
     * @param array $query
     * @return array
     */
    function filter_product_gallery_images_in_media_library($query)
    {
        // Check if we're on the product gallery page or have the filter parameter
        // For AJAX requests (grid view), check $_POST['query']
        $is_product_gallery_page = (isset($_GET['page']) && $_GET['page'] === 'product-gallery') 
                                 || (isset($_GET['product_gallery_filter']) && $_GET['product_gallery_filter'] === 'yes')
                                 || (isset($_POST['query']['product_gallery_filter']) && $_POST['query']['product_gallery_filter'] === 'yes');
        
        if ($is_product_gallery_page) {
            // Show ONLY product gallery images
            $query['meta_query'] = array(
                array(
                    'key' => '_growtype_form_product_gallery',
                    'value' => 'yes',
                    'compare' => '='
                )
            );
        } else {
            // Hide product gallery images (default behavior)
            $query['meta_query'] = array(
                array(
                    'key' => '_growtype_form_product_gallery',
                    'compare' => 'NOT EXISTS'
                )
            );
        }
        
        return $query;
    }
    
    /**
     * Filter product gallery in list mode (for upload.php page)
     * 
     * @param WP_Query $query
     */
    function filter_product_gallery_in_list_mode($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        global $pagenow;
        if ($pagenow !== 'upload.php') {
            return;
        }
        
        $is_product_gallery = isset($_GET['product_gallery_filter']) && $_GET['product_gallery_filter'] === 'yes';
        

        
        if ($is_product_gallery) {
            // Show ONLY product gallery images
            $meta_query = array(
                array(
                    'key' => '_growtype_form_product_gallery',
                    'value' => 'yes',
                    'compare' => '='
                )
            );
            $query->set('meta_query', $meta_query);
        } else {
            // Hide product gallery images
            $meta_query = array(
                array(
                    'key' => '_growtype_form_product_gallery',
                    'compare' => 'NOT EXISTS'
                )
            );
            $query->set('meta_query', $meta_query);
        }
    }
    
    /**
     * Add Product Gallery submenu under Media
     */
    function add_product_gallery_submenu()
    {
        add_submenu_page(
            'upload.php',                    // Parent slug (Media menu)
            'Product Gallery',               // Page title
            'Product Gallery',               // Menu title
            'upload_files',                  // Capability
            'product-gallery',               // Menu slug
            array($this, 'render_product_gallery_page')  // Callback function
        );
    }
    
    /**
     * Render Product Gallery page
     */
    function render_product_gallery_page()
    {
        // Redirect to upload.php with our custom filter
        wp_redirect(admin_url('upload.php?product_gallery_filter=yes'));
        exit;
    }
    
    /**
     * Fix parent menu highlighting
     */
    function fix_product_gallery_menu_highlight($parent_file)
    {
        global $pagenow;
        
        if ($pagenow === 'upload.php' && isset($_GET['product_gallery_filter']) && $_GET['product_gallery_filter'] === 'yes') {
            $parent_file = 'upload.php';
        }
        
        return $parent_file;
    }
    
    /**
     * Fix submenu highlighting
     */
    function fix_product_gallery_submenu_highlight($submenu_file)
    {
        global $pagenow;
        
        if ($pagenow === 'upload.php' && isset($_GET['product_gallery_filter']) && $_GET['product_gallery_filter'] === 'yes') {
            $submenu_file = 'product-gallery';
        }
        
        return $submenu_file;
    }
}
