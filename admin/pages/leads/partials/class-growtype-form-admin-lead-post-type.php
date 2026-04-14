<?php

/**
 * Handles post type registration logic for gf_lead.
 */
class Growtype_Form_Admin_Lead_Post_Type
{
    public function __construct()
    {
        add_action('init', array ($this, 'register_post_type'));

        /**
         * Disable lead translation
         */
        add_filter('qtranxf_post_type_optional', array ($this, 'qtranxf_post_type_optional'));

        /**
         * Delete lead
         */
        add_action('delete_user', array ($this, 'delete_lead'));
    }

    function delete_lead($user_id)
    {
        $user_data = get_userdata($user_id);
        $lead = Growtype_Form_Admin_Lead_Crud::get_by_title($user_data->user_email);

        if (!empty($lead)) {
            wp_delete_post($lead->ID, true);
        }
    }

    function qtranxf_post_type_optional($post_types)
    {
        return $post_types;
    }

    /**
     * Post type
     */
    function register_post_type()
    {
        $labels = array (
            'name' => _x('Leads', 'Post type general name', 'textdomain'),
            'singular_name' => _x('Lead', 'Post type singular name', 'textdomain'),
            'menu_name' => _x('Leads', 'Admin Menu text', 'textdomain'),
            'name_admin_bar' => _x('Lead', 'Add New on Toolbar', 'textdomain'),
            'add_new' => __('Add New', 'textdomain'),
            'add_new_item' => __('Add New Lead', 'textdomain'),
            'new_item' => __('New Lead', 'textdomain'),
            'edit_item' => __('Edit Lead', 'textdomain'),
            'view_item' => __('View Lead', 'textdomain'),
            'all_items' => __('Leads', 'textdomain'),
            'search_items' => __('Search Leads', 'textdomain'),
            'parent_item_colon' => __('Parent Leads:', 'textdomain'),
            'not_found' => __('No Leads found.', 'textdomain'),
            'not_found_in_trash' => __('No Leads found in Trash.', 'textdomain'),
            'featured_image' => _x('Lead Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
            'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'archives' => _x('Lead archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
            'insert_into_item' => _x('Insert into Lead', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this Lead', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
            'filter_items_list' => _x('Filter Leads list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
            'items_list_navigation' => _x('Leads list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
            'items_list' => _x('Leads list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
        );

        $args = array (
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'rewrite' => array ('slug' => Growtype_Form_Admin_Lead::POST_TYPE_NAME),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            //            'menu_position' => 902,
//            'supports' => array ('title', 'editor'),
            'supports' => array ('title'),
            'menu_icon' => 'dashicons-forms',
            'show_in_rest' => false,
            'show_in_menu' => 'edit.php?post_type=' . Growtype_Form_Admin_Form::POST_TYPE_NAME,
        );

        register_post_type(Growtype_Form_Admin_Lead::POST_TYPE_NAME, $args);
    }
}
