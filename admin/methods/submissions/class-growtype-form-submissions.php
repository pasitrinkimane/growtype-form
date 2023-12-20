<?php

/**
 *
 */
class Growtype_Form_Submissions
{
    const POST_TYPE_NAME = 'gf_submission';

    const META_BOXES = [
        [
            'id' => 'form_details',
            'title' => 'Form details',
            'fields' => [
                [
                    'title' => 'Form name',
                    'key' => 'form_name'
                ],
                [
                    'type' => 'textarea',
                    'title' => 'Submitted values',
                    'key' => 'submitted_values'
                ],
                [
                    'title' => 'http_x_forwarded_for',
                    'key' => 'server_http_x_forwarded_for'
                ],
                [
                    'title' => 'remote_addr',
                    'key' => 'server_remote_addr'
                ],
                [
                    'title' => 'Attachments',
                    'key' => 'uploaded_attachments'
                ],
            ]
        ]
    ];

    public function __construct()
    {
        /**
         * Post type
         */
        add_action('init', array ($this, 'register_post_type'));

        /**
         * Meta fields
         */
        add_action('init', array ($this, 'add_meta_fields'));
        add_action('add_meta_boxes', array ($this, 'add_custom_meta_boxes'));
        add_action('save_post', array ($this, 'save_custom_meta_boxes'));

        /**
         * Admin columns
         */
        add_filter('manage_' . self::POST_TYPE_NAME . '_posts_columns', array ($this, 'set_custom_posts_columns'));
        add_action('manage_' . self::POST_TYPE_NAME . '_posts_custom_column', array ($this, 'set_custom_posts_custom_column'), 10, 2);

        /**
         * Delete post
         */
        add_action('admin_init', array ($this, 'init_post_delete'));
    }

    function init_post_delete()
    {
        add_action('before_delete_post', array ($this, 'before_delete_post'), 0);
    }

    function before_delete_post($pid)
    {
        $uploaded_attachments = !empty(get_post_meta($pid, 'uploaded_attachments', true)) ? json_decode(get_post_meta($pid, 'uploaded_attachments', true), true) : [];

        foreach ($uploaded_attachments as $attachment) {
            wp_delete_attachment($attachment['attachment_id']);
        }
    }

    /**
     * Post type
     */
    function register_post_type()
    {
        $labels = array (
            'name' => _x('GF - Submissions', 'Post type general name', 'textdomain'),
            'singular_name' => _x('Submission', 'Post type singular name', 'textdomain'),
            'menu_name' => _x('GF - Submissions', 'Admin Menu text', 'textdomain'),
            'name_admin_bar' => _x('Submission', 'Add New on Toolbar', 'textdomain'),
            'add_new' => __('Add New', 'textdomain'),
            'add_new_item' => __('Add New Submission', 'textdomain'),
            'new_item' => __('New Submission', 'textdomain'),
            'edit_item' => __('Edit Submission', 'textdomain'),
            'view_item' => __('View Submission', 'textdomain'),
            'all_items' => __('All Submissions', 'textdomain'),
            'search_items' => __('Search Submissions', 'textdomain'),
            'parent_item_colon' => __('Parent Submissions:', 'textdomain'),
            'not_found' => __('No Submissions found.', 'textdomain'),
            'not_found_in_trash' => __('No Submissions found in Trash.', 'textdomain'),
            'featured_image' => _x('Submission Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain'),
            'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain'),
            'archives' => _x('Submission archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain'),
            'insert_into_item' => _x('Insert into Submission', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this Submission', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain'),
            'filter_items_list' => _x('Filter Submissions list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain'),
            'items_list_navigation' => _x('Submissions list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain'),
            'items_list' => _x('Submissions list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain'),
        );

        $args = array (
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'rewrite' => array ('slug' => self::POST_TYPE_NAME),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array ('title', 'editor'),
            'show_in_rest' => false,
        );

        register_post_type(self::POST_TYPE_NAME, $args);
    }

    /**
     * Meta fields
     */
    function add_meta_fields()
    {
        add_post_type_support(self::POST_TYPE_NAME, 'custom-fields');
    }

    function add_custom_meta_boxes()
    {
        foreach (self::META_BOXES as $box) {
            add_meta_box(
                $box['id'],
                $box['title'],
                array ($this, 'render_custom_meta_box'),
                self::POST_TYPE_NAME,
                'normal',
                'high',
                array ('fields' => $box['fields'])
            );
        }
    }

    function render_custom_meta_box($post, $params)
    {
        ?>
        <div style="display:flex;gap: 10px; flex-direction: column;">
            <?php
            foreach ($params['args']['fields'] as $field) {
                $meta_value = get_post_meta($post->ID, $field['key'], true); ?>
                <div style="display: flex;gap:10px;">
                    <label style="min-width: 150px;" for="<?php echo $field['key'] ?>"><?php echo $field['title'] ?>:</label>
                    <?php if (isset($field['type']) && $field['type'] === 'textarea') { ?>
                        <textarea style="width: 100%;" disabled id="<?php echo $field['key'] ?>" name="<?php echo $field['key'] ?>" rows="4" cols="50"><?php echo esc_attr($meta_value); ?></textarea>
                    <?php } else { ?>
                        <input style="width: 100%;" disabled type="text" id="<?php echo $field['key'] ?>" name="<?php echo $field['key'] ?>" value="<?php echo esc_attr($meta_value); ?>">
                    <?php } ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    function save_custom_meta_boxes($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        foreach (self::META_BOXES as $box) {
            foreach ($box['fields'] as $field) {
                if (isset($_POST[$field['key']])) {
                    update_post_meta($post_id, $field['key'], sanitize_text_field($_POST[$field['key']]));
                }
            }
        }
    }

    /**
     * Admin columns
     */
    function set_custom_posts_columns($columns)
    {
        foreach (self::META_BOXES as $box) {
            foreach ($box['fields'] as $field) {
                $columns[$field['key']] = $field['title'];
            }
        }

        return $columns;
    }

    function set_custom_posts_custom_column($column, $post_id)
    {
        foreach (self::META_BOXES as $box) {
            foreach ($box['fields'] as $field) {
                if ($column === $field['key']) {
                    echo get_post_meta($post_id, $field['key'], true);
                }
            }
        }
    }
}
