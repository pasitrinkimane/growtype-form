<?php

/**
 *
 */
class Growtype_Form_Admin_Lead
{
    const POST_TYPE_NAME = 'gf_lead';

    const META_BOXES = [
        [
            'id' => 'lead_details',
            'title' => 'Lead details',
            'fields' => []
        ]
    ];

    public function __construct()
    {
        /**
         * Post type
         */
        add_action('init', array ($this, 'register_post_type'));

        /**
         * Admin columns
         */
        add_filter('manage_' . self::POST_TYPE_NAME . '_posts_columns', array ($this, 'set_custom_posts_columns'));
        add_action('manage_' . self::POST_TYPE_NAME . '_posts_custom_column', array ($this, 'set_custom_posts_custom_column'), 10, 2);

        /**
         * Add custom fields
         */
        add_action('add_meta_boxes', array ($this, 'add_custom_meta_boxes'));
        add_action('save_post', array ($this, 'save_custom_meta_boxes'));

        /**
         * Actions
         */
        add_action('wp_loaded', array ($this, 'process_custom_bulk_actions'));

        /**
         * Bulk actions scripts
         */
        add_action('admin_head-edit.php', array ($this, 'custom_bulk_actions_scripts'));

        /**
         * Disable lead translation
         */
        add_filter('qtranxf_post_type_optional', array ($this, 'qtranxf_post_type_optional'));

        /**
         * Admin footer
         */
        add_action('admin_footer', array ($this, 'admin_footer_extend'));

        /**
         * Delete lead
         */
        add_action('delete_user', array ($this, 'delete_lead'));

        /**
         * Load extra methods
         */
        $this->load_methods();
    }

    function delete_lead($user_id)
    {
        $user_data = get_userdata($user_id);
        $lead = self::get_by_title($user_data->user_email);

        if (!empty($lead)) {
            wp_delete_post($lead->ID, true);
        }
    }

    function qtranxf_post_type_optional($post_types)
    {
        return $post_types;
    }

    function add_custom_meta_boxes()
    {
        foreach (self::get_meta_boxes() as $box) {
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
                    <label style="min-width: 150px;max-width: 150px;" for="<?php echo $field['key'] ?>"><?php echo $field['title'] ?>:</label>
                    <?php if (isset($field['type']) && $field['type'] === 'textarea') { ?>
                        <textarea style="width: 100%;" id="<?php echo $field['key'] ?>" name="<?php echo $field['key'] ?>" rows="10" cols="50"><?php echo esc_attr($meta_value); ?></textarea>
                    <?php } else { ?>
                        <input style="width: 100%;" type="text" id="<?php echo $field['key'] ?>" name="<?php echo $field['key'] ?>" value="<?php echo esc_attr($meta_value); ?>">
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

        foreach (self::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                if (isset($_POST[$field['key']])) {
                    update_post_meta($post_id, $field['key'], sanitize_text_field($_POST[$field['key']]));
                }
            }
        }
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
            'rewrite' => array ('slug' => self::POST_TYPE_NAME),
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

        register_post_type(self::POST_TYPE_NAME, $args);
    }

    /**
     * Admin columns
     */
    function set_custom_posts_columns($columns)
    {
        foreach (self::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                $columns[$field['key']] = $field['title'];
            }
        }

        return $columns;
    }

    function set_custom_posts_custom_column($column, $post_id)
    {
        foreach (self::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                if ($column === $field['key']) {
                    echo get_post_meta($post_id, $field['key'], true);
                }
            }
        }
    }

    public static function insert($data)
    {
        if (isset($data['title']) && !empty($data['title'])) {
            $existing_posts = self::get_all_by_title($data['title']);

            if (empty($existing_posts)) {
                wp_insert_post([
                    'post_type' => self::POST_TYPE_NAME,
                    'post_title' => $data['title'],
                    'post_status' => $data['status'] ?? 'publish'
                ]);
            }
        }
    }

    public static function get_meta_boxes()
    {
        $meta_boxes = self::META_BOXES;

        return apply_filters('growtype_form_lead_meta_boxes', $meta_boxes);
    }

    public static function get_all_by_title($title)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type='%s' AND post_title= '%s'", self::POST_TYPE_NAME, esc_sql($title)));
    }

    public static function get_by_title($title)
    {
        if (count(self::get_all_by_title($title)) > 1) {
            error_log('!!!IMPORTANT!!! Multiple leads found with the same title: ' . $title);
            return null;
        }

        return self::get_all_by_title($title)[0] ?? null;
    }

    function process_custom_bulk_actions()
    {
        if (isset($_GET['post_type']) && $_GET['post_type'] === self::POST_TYPE_NAME && isset($_GET['lead_fetch_action']) && $_GET['lead_fetch_action'] !== '') {
            $action = $_GET['lead_fetch_action'];

            if ($action === 'fetch_emails') {
                $args = array (
                    'post_type' => Growtype_Form_Admin_Submission::POST_TYPE_NAME,
                    'posts_per_page' => -1,
                    'post_status' => 'any',
                );

                $submissions = get_posts($args);

                foreach ($submissions as $submission) {
                    $email = Growtype_Form_Admin_Submission::get_email($submission->ID);

                    self::insert([
                        'title' => $email,
                        'status' => 'publish'
                    ]);
                }

                $users = get_users();

                foreach ($users as $user) {
                    $email = $user->data->user_email;

                    self::insert([
                        'title' => $email,
                        'status' => 'publish'
                    ]);
                }
            }

            wp_redirect(admin_url('edit.php?post_type=' . self::POST_TYPE_NAME));
            exit;
        }
    }

    function custom_bulk_actions_scripts()
    {
        global $typenow;

        if ($typenow === self::POST_TYPE_NAME && count($_GET) < 2) {
            $fetch_emails_url = esc_url(admin_url('edit.php?post_type=' . self::POST_TYPE_NAME . '&lead_fetch_action=fetch_emails'));
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('<a href="<?php echo $fetch_emails_url ?>" class="button button-primary" style="top: 9px;position: relative;">Fetch emails</a>').insertBefore($('.wp-header-end'));
                });
            </script>
            <?php
        }
    }

    public static function is_edit_post_type()
    {
        global $post;

        return (isset($_GET['action']) && $_GET['action'] === 'edit') && !empty($post) && $post->post_type === self::POST_TYPE_NAME;
    }

    function admin_footer_extend()
    {
        global $pagenow;

        if (self::is_edit_post_type()) {
            Growtype_Form_Admin::init_json_editor('#events_log', [
                'height' => '600px'
            ]);
        }

        /**
         * Filter by day extended
         */
        if (isset($_GET['post_type']) && $_GET['post_type'] === self::POST_TYPE_NAME && $pagenow === 'edit.php') {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    var currentDate = new Date();
                    var year = currentDate.getFullYear();
                    var month = ('0' + (currentDate.getMonth() + 1)).slice(-2);
                    var day = ('0' + currentDate.getDate()).slice(-2);
                    var currentDayValue = year + month + day;
                    var currentDayText = 'Today';

                    var yesterdayDate = new Date(currentDate);
                    yesterdayDate.setDate(yesterdayDate.getDate() - 1);
                    var yYear = yesterdayDate.getFullYear();
                    var yMonth = ('0' + (yesterdayDate.getMonth() + 1)).slice(-2);
                    var yDay = ('0' + yesterdayDate.getDate()).slice(-2);
                    var yesterdayValue = yYear + yMonth + yDay;
                    var yesterdayText = 'Yesterday';

                    var filterSelect = $('select[name="m"]');
                    if (filterSelect.length > 0) {
                        $('<option value="' + yesterdayValue + '">' + yesterdayText + '</option>').insertAfter(filterSelect.find('option').first());
                        $('<option value="' + currentDayValue + '">' + currentDayText + '</option>').insertAfter(filterSelect.find('option').first());
                    }

                    filterSelect.change(function () {
                        if ($(this).val() === currentDayValue) {
                            $('input[name="filter_by_day"]').val(currentDayValue);
                        } else {
                            $('input[name="filter_by_day"]').val('');
                        }
                    });

                    function getUrlParameter(name) {
                        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                        var results = regex.exec(window.location.search);
                        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                    }

                    var dayParam = getUrlParameter('m');
                    if (dayParam) {
                        filterSelect.val(dayParam).change();
                    }
                });
            </script>
            <?php
        }
    }

    public static function update_events_log($id, $details)
    {
        $events_log = self::get_events_log($id);

        /**
         * Remove body from log
         */
        if (isset($details['data']['body'])) {
            $details['data']['body'] = 'Body removed from log for security reasons.';
        }

        $success = $details['success'] ?? '';

        $events_log[] = [
            'date' => wp_date('Y-m-d H:i:s'),
            'details' => $details['data'] ?? [],
            'success' => $success ? 'true' : 'false'
        ];

        update_post_meta($id, 'events_log', json_encode($events_log));
    }

    public static function get_events_log($id)
    {
        $events_log = get_post_meta($id, 'events_log', true);
        $events_log = !empty($events_log) ? json_decode($events_log, true) : [];

        return $events_log;
    }

    public function load_methods()
    {
        /**
         * Custom actions
         */
        include_once 'partials/class-growtype-form-admin-lead-custom-actions.php';
        new Growtype_Form_Admin_Lead_Custom_Actions();
    }
}
