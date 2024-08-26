<?php

/**
 *
 */
class Growtype_Form_Admin_Form
{
    const POST_TYPE_NAME = 'gf_form';

    public function __construct()
    {
        /**
         * Post type
         */
        add_action('init', array ($this, 'register_post_type'));

        /**
         * Admin footer
         */
        add_action('admin_footer', array ($this, 'admin_footer_extend'));

        add_action('add_meta_boxes', array ($this, 'add_custom_meta_box'));
        add_action('save_post', array ($this, 'save_custom_meta_box_data'));
    }

    public static function is_edit_post_type()
    {
        global $post;

        return (isset($_GET['action']) && $_GET['action'] === 'edit') && !empty($post) && $post->post_type === self::POST_TYPE_NAME;
    }

    function add_custom_meta_box()
    {
        if (self::is_edit_post_type()) {
            add_meta_box(
                'json_content', // Unique ID
                'Content', // Box title
                array ($this, 'render_custom_meta_box'), // Callback function to render the contents of the meta box
                self::POST_TYPE_NAME, // Post type
                'normal', // Context
                'high' // Priority
            );
        }
    }

    function render_custom_meta_box($post)
    {
        $json_content = get_post_meta($post->ID, 'json_content', true);

        ?>
        <textarea id="json_content" class="growtype_form_json_content" name="json_content" rows="50" cols="50" style="width: 100%;"><?php echo esc_attr($json_content) ?></textarea>
        <?php
    }

    function save_custom_meta_box_data($post_id)
    {
        if (isset($_POST['post_type']) && $_POST['post_type'] === self::POST_TYPE_NAME) {
            if (!isset($_POST['json_content'])) {
                return;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            if (isset($_POST['json_content'])) {
                update_post_meta($post_id, 'json_content', $_POST['json_content']);
            }
        }
    }

    function admin_footer_extend()
    {
        if (self::is_edit_post_type()) {
            Growtype_Form_Admin::init_json_editor();
        }
    }

    /**
     * Post type
     */
    function register_post_type()
    {
        $labels = array (
            'name' => _x('Forms', 'Post type general name', 'textdomain'),
            'singular_name' => _x('Submission', 'Post type singular name', 'textdomain'),
            'menu_name' => _x('Forms', 'Admin Menu text', 'textdomain'),
            'name_admin_bar' => _x('Submission', 'Add New on Toolbar', 'textdomain'),
            'add_new' => __('Add New', 'textdomain'),
            'add_new_item' => __('Add New Submission', 'textdomain'),
            'new_item' => __('New Submission', 'textdomain'),
            'edit_item' => __('Edit Submission', 'textdomain'),
            'view_item' => __('View Submission', 'textdomain'),
            'all_items' => __('Forms', 'textdomain'),
            'search_items' => __('Search Forms', 'textdomain'),
            'parent_item_colon' => __('Parent Forms:', 'textdomain'),
            'not_found' => __('No Forms found.', 'textdomain'),
            'not_found_in_trash' => __('No Forms found in Trash.', 'textdomain'),
            'featured_image' => _x('Submission Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
            'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'archives' => _x('Submission archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
            'insert_into_item' => _x('Insert into Submission', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this Submission', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
            'filter_items_list' => _x('Filter Forms list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
            'items_list_navigation' => _x('Forms list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
            'items_list' => _x('Forms list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
        );

        $args = array (
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => current_user_can('administrator') ? true : false,
            'query_var' => false,
            'rewrite' => array ('slug' => self::POST_TYPE_NAME),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
//            'menu_position' => 902,
//            'supports' => array ('title', 'editor'),
            'supports' => array ('title'),
            'menu_icon' => 'dashicons-forms',
            'show_in_rest' => false,
        );

        register_post_type(self::POST_TYPE_NAME, $args);
    }
}
