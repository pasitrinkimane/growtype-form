<?php

use EmailValidator\EmailValidator;

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
        add_action('admin_head-edit.php', array ($this, 'custom_actions_scripts'));

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
         * Fetch emails
         */
        add_action('wp_ajax_growtype_form_admin_fetch_emails', array ($this, 'fetch_emails_callback'));

        /**
         * Validate emails
         */
        add_action('wp_ajax_growtype_form_admin_validate_emails', array ($this, 'validate_emails_callback'));

        /**
         * Validate emails
         */
        add_action('admin_post_growtype_form_admin_export_emails', array ($this, 'export_emails_callback'));
        add_action('admin_post_growtype_form_admin_export_leads', array ($this, 'export_leads_callback'));

        /**
         * Load extra methods
         */
        $this->load_methods();
    }

    public function load_methods()
    {
        /**
         * Custom actions
         */
        include_once 'partials/class-growtype-form-admin-lead-custom-actions.php';
        new Growtype_Form_Admin_Lead_Custom_Actions();
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
        <div style="display: flex; gap: 10px; flex-direction: column;">
            <?php
            foreach ($params['args']['fields'] as $field) {
                $meta_value = get_post_meta($post->ID, $field['key'], true);
                $type = $field['type'] ?? 'text';
                ?>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <label style="min-width: 150px; max-width: 150px;" for="<?php echo esc_attr($field['key']); ?>">
                        <?php echo esc_html($field['title']); ?>:
                    </label>

                    <?php if ($type === 'textarea') { ?>
                        <textarea
                            style="width: 100%;"
                            id="<?php echo esc_attr($field['key']); ?>"
                            name="<?php echo esc_attr($field['key']); ?>"
                            rows="5"
                        ><?php echo esc_textarea($meta_value); ?></textarea>

                    <?php } elseif ($type === 'checkbox') { ?>
                        <input
                            type="checkbox"
                            id="<?php echo esc_attr($field['key']); ?>"
                            name="<?php echo esc_attr($field['key']); ?>"
                            value="1"
                            <?php checked($meta_value, '1'); ?>
                        />

                    <?php } else { ?>
                        <input
                            style="width: 100%;"
                            type="text"
                            id="<?php echo esc_attr($field['key']); ?>"
                            name="<?php echo esc_attr($field['key']); ?>"
                            value="<?php echo esc_attr($meta_value); ?>"
                        />
                    <?php } ?>
                </div>
            <?php } ?>
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
                $key = $field['key'];
                $type = $field['type'] ?? 'text';

                if ($type === 'checkbox') {
                    // Save 1 if checked, 0 if not set
                    $value = isset($_POST[$key]) ? '1' : '0';
                    update_post_meta($post_id, $key, $value);
                } else {
                    if (isset($_POST[$key])) {
                        update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
                    }
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
                    if (in_array($field['key'], ['user_is_verified', 'auth_method'])) {
                        $user = get_user_by('email', get_the_title($post_id));
                        if (!empty($user)) {
                            if ($field['key'] === 'user_is_verified') {
                                echo get_user_meta($user->ID, 'is_verified', true);
                            } elseif ($field['key'] === 'auth_method') {
                                echo get_user_meta($user->ID, 'auth_method', true);
                            }
                        }
                    } else {
                        echo get_post_meta($post_id, $field['key'], true);
                    }
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

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type='%s' AND post_title= '%s'", self::POST_TYPE_NAME, $title));
    }

    public static function get_by_title($title)
    {
        if (count(self::get_all_by_title($title)) > 1) {
            error_log('Growtype Form - !!!IMPORTANT!!! Multiple leads found with the same title: ' . $title);
            return null;
        }

        return self::get_all_by_title($title)[0] ?? null;
    }

    function process_custom_bulk_actions()
    {
        if (current_user_can('manage_options') && isset($_GET['post_type']) && $_GET['post_type'] === self::POST_TYPE_NAME && isset($_GET['lead_fetch_action']) && $_GET['lead_fetch_action'] !== '') {
            $action = $_GET['lead_fetch_action'];

            if ($action === 'fetch_emails') {
                self::fetch_emails();
            }

            wp_redirect(admin_url('edit.php?post_type=' . self::POST_TYPE_NAME));
            exit;
        }
    }

    public static function fetch_emails($limit = 100, $offset = 0)
    {
        global $wpdb;

        $lead_post_type = Growtype_Form_Admin_Lead::POST_TYPE_NAME;
        $emails_to_insert = [];
        $total_fetched = 0;

        // 1. Fetch submission emails
        $submissions_sql = $wpdb->prepare(
            "SELECT pm.meta_value AS submitted_values
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE p.post_type = %s AND pm.meta_key = 'submitted_values'
         LIMIT %d OFFSET %d",
            Growtype_Form_Admin_Submission::POST_TYPE_NAME,
            $limit,
            $offset
        );

        $results = $wpdb->get_results($submissions_sql);
        $total_fetched += count($results);

        foreach ($results as $row) {
            $data = json_decode($row->submitted_values, true) ?? [];
            if (!empty($data['data']['email'])) {
                $email = filter_var($data['data']['email'], FILTER_VALIDATE_EMAIL);
                if ($email) {
                    $emails_to_insert[$email] = $email;
                }
            }
        }

        // 2. Fetch user emails
        $user_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_email FROM {$wpdb->users} ORDER BY ID ASC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );

        $total_fetched += count($user_results);

        foreach ($user_results as $user) {
            $email = filter_var($user->user_email, FILTER_VALIDATE_EMAIL);
            if ($email) {
                $emails_to_insert[$email] = $email;
            }
        }

        // 3. Filter out emails that already exist in leads
        if (!empty($emails_to_insert)) {
            $placeholders = implode(',', array_fill(0, count($emails_to_insert), '%s'));
            $existing_emails = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_title FROM {$wpdb->posts} WHERE post_type=%s AND post_title IN ($placeholders)",
                    array_merge([$lead_post_type], array_keys($emails_to_insert))
                )
            );

            // Remove existing emails from insert list
            foreach ($existing_emails as $email) {
                unset($emails_to_insert[$email]);
            }
        }

        // 4. Insert remaining emails
        foreach ($emails_to_insert as $email) {
            wp_insert_post([
                'post_type' => $lead_post_type,
                'post_title' => $email,
                'post_status' => 'publish'
            ]);
        }

        return [
            'emails_inserted' => count($emails_to_insert),
            'total_fetched' => $total_fetched,
        ];
    }

    function fetch_emails_callback()
    {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 1000;

        $fetch_emails = self::fetch_emails($limit, $offset);

        wp_send_json_success(
            $fetch_emails
        );
    }

    function validate_emails_callback()
    {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 100;

        $args = [
            'post_type' => 'gf_lead',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'is_validated',
                    'value' => '1',
                    'compare' => '!=',
                ],
                [
                    'key' => 'is_validated',
                    'compare' => 'NOT EXISTS',
                ]
            ],
            'fields' => 'ids',
        ];

        $leads_query = new WP_Query($args);

        if (!$leads_query->have_posts()) {
            wp_send_json_success([
                'emails_valid' => 0,
                'total_fetched' => 0
            ]);
        }

        $emails = $leads_query->posts;

        $validate_emails = self::validate_emails($emails);

        wp_send_json_success($validate_emails);
    }

    function export_emails_callback()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 'Error', ['response' => 403]);
        }

        // Get all gf_lead posts
        $args = [
            'post_type' => 'gf_lead',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'is_validated',
                    'value' => '1',
                    'compare' => '='
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => 'newsletter_unsubscribed',
                        'value' => '1',
                        'compare' => '!='
                    ],
                    [
                        'key' => 'newsletter_unsubscribed',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ]
        ];

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            wp_die('No leads found.');
        }

        // Prepare CSV headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gf_leads_exported_emails_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');

        // Define CSV column headers
        fputcsv($output, ['ID', 'Email', 'Date']);

        // Loop through posts
        foreach ($query->posts as $lead_id) {
            $email = get_the_title($lead_id);
            $date = get_the_date('Y-m-d H:i:s', $lead_id);

            fputcsv($output, [
                $lead_id,
                $email,
                $date,
            ]);
        }

        fclose($output);
        exit;
    }

    function export_leads_callback()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 'Error', ['response' => 403]);
        }

        // Get all gf_lead posts
        $args = [
            'post_type' => 'gf_lead',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            wp_die('No leads found.');
        }

        // Prepare CSV headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gf_leads_exported_leads_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');

        // Define CSV column headers
        fputcsv($output, ['ID', 'Email', 'Unsubscribed', 'Date']);

        // Loop through posts
        foreach ($query->posts as $lead_id) {
            $email = get_the_title($lead_id);
            $date = get_the_date('Y-m-d H:i:s', $lead_id);
            $unsubscribed = get_post_meta($lead_id, 'newsletter_unsubscribed', true);

            fputcsv($output, [
                $lead_id,
                $email,
                $unsubscribed,
                $date,
            ]);
        }

        fclose($output);
        exit;
    }

    public static function validate_emails($emails)
    {
        $countValidated = 0;
        foreach ($emails as $lead_id) {
            $email = get_the_title($lead_id);
            if (!$email) {
                continue;
            }

            $email_validation = Growtype_Form_Crud::first_level_email_validation($email);
            $isValid = $email_validation['success'];

            update_post_meta($lead_id, 'is_validated', true);
            update_post_meta($lead_id, 'validation_status', $isValid ? 'valid' : 'invalid');

            if ($isValid) {
                $countValidated++;
            }
        }

        return [
            'emails_valid' => $countValidated,
            'total_fetched' => count($emails)
        ];
    }

    public static function is_edit_post_type()
    {
        global $post;

        return (isset($_GET['action']) && $_GET['action'] === 'edit') && !empty($post) && $post->post_type === self::POST_TYPE_NAME;
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
                    var filterSelect = $('select[name="m"]');
                    if (filterSelect.length > 0) {
                        var currentDate = new Date();
                        var optionsHtml = ''; // Collect options as HTML

                        // Generate options for the last 7 days, starting with today
                        for (let i = 0; i < 7; i++) {
                            var tempDate = new Date(currentDate);
                            tempDate.setDate(currentDate.getDate() - i);

                            // Format date as YYYY-MM-DD
                            var year = tempDate.getFullYear();
                            var month = ('0' + (tempDate.getMonth() + 1)).slice(-2);
                            var day = ('0' + tempDate.getDate()).slice(-2);
                            var formattedDate = year + '-' + month + '-' + day;

                            // Add labels for Today and Yesterday
                            var label = '';
                            if (i === 0) {
                                label = ' (Today)';
                            } else if (i === 1) {
                                label = ' (Yesterday)';
                            }

                            // Build the option HTML
                            optionsHtml += '<option value="' + formattedDate + '">' + formattedDate + label + '</option>';
                        }

                        // Prepend the options to the dropdown
                        filterSelect.prepend(optionsHtml);

                        // Handle select change
                        filterSelect.change(function () {
                            var selectedValue = $(this).val();
                            if (selectedValue) {
                                $('input[name="filter_by_day"]').val(selectedValue);
                            } else {
                                $('input[name="filter_by_day"]').val('');
                            }
                        });

                        // Get URL parameter
                        function getUrlParameter(name) {
                            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                            var results = regex.exec(window.location.search);
                            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                        }

                        // Set selected value from URL if present
                        var dayParam = getUrlParameter('m');
                        if (dayParam) {
                            filterSelect.val(dayParam).change();
                        }
                    }
                });

            </script>
            <?php
        }
    }

    function custom_actions_scripts()
    {
        global $typenow;

        if ($typenow === self::POST_TYPE_NAME) {
            $fetch_emails_url = esc_url(admin_url('edit.php?post_type=' . self::POST_TYPE_NAME . '&lead_fetch_action=fetch_emails'));
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('<a href="#" class="button button-primary" style="top: 9px; position: relative;">Fetch emails</a>')
                        .insertBefore($('.wp-header-end'))
                        .on('click', function (e) {
                            e.preventDefault();

                            var offset = 0;
                            var batchSize = 500;
                            var batchNr = 0;

                            var alertBox = $('<div id="emails-fetch-alert" style="position: fixed; top: 50px; right: 30px; background: #008000; color: #fff; padding: 10px; border-radius: 5px; z-index: 9999;">Fetching...</div>');
                            $('body').append(alertBox);

                            function fetchBatch() {
                                batchNr++;
                                $.ajax({
                                    url: window.ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'growtype_form_admin_fetch_emails',
                                        offset: offset,
                                        limit: batchSize
                                    },
                                    success: function (response) {
                                        var emails_inserted = response.data.emails_inserted;
                                        var total_fetched = response.data.total_fetched;

                                        if (total_fetched !== 0) {
                                            alertBox.text('Batch ' + batchNr + '. Processed records: ' + total_fetched + '. New emails inserted:' + emails_inserted);
                                            offset += batchSize;
                                            fetchBatch();
                                        } else {
                                            alertBox.text('All emails processed.');
                                            setTimeout(() => window.location.reload(), 5000);
                                        }
                                    },
                                    error: function () {
                                        alertBox.text('Error fetching emails!');
                                        setTimeout(() => alertBox.fadeOut(), 5000);
                                    }
                                });
                            }

                            fetchBatch();
                        });

                    $('<a href="#" class="button button-primary" style="top: 9px; margin-left: 5px; position: relative;">Validate emails</a>')
                        .insertBefore($('.wp-header-end'))
                        .on('click', function (e) {
                            e.preventDefault();

                            var offset = 0;
                            var batchSize = 100;
                            var batchNr = 0;

                            var alertBox = $('<div id="emails-fetch-alert" style="position: fixed; top: 50px; right: 30px; background: #008000; color: #fff; padding: 10px; border-radius: 5px; z-index: 9999;">Validating...</div>');
                            $('body').append(alertBox);

                            function fetchBatch() {
                                batchNr++;

                                $.ajax({
                                    url: window.ajaxurl,
                                    type: 'POST',
                                    data: {
                                        action: 'growtype_form_admin_validate_emails',
                                        offset: offset,
                                        limit: batchSize
                                    },
                                    success: function (response) {
                                        var emails_valid = response.data.emails_valid;
                                        var total_fetched = response.data.total_fetched;

                                        if (total_fetched !== 0) {
                                            alertBox.text('Batch ' + batchNr + '. Processed records: ' + total_fetched + '. New emails validated: ' + emails_valid);
                                            offset += batchSize;
                                            fetchBatch();
                                        } else {
                                            alertBox.text('All emails processed.');
                                            setTimeout(() => window.location.reload(), 5000);
                                        }
                                    },
                                    error: function () {
                                        alertBox.text('Error validating emails!');
                                        setTimeout(() => alertBox.fadeOut(), 5000);
                                    }
                                });
                            }

                            fetchBatch();
                        });

                    $('<div class="export-dropdown" style="display: inline-block;position: relative;margin-left: 5px;vertical-align: middle;margin-top: -10px;">' +
                        '<button class="button button-primary">Export leads ▼</button>' +
                        '<ul style="display:none; position:absolute; top:100%; left:0; background:#fff; border:1px solid #ccc; list-style:none; padding:0; margin:0; min-width:150px; z-index:9999;">' +
                        '<li style="padding:8px; cursor:pointer;">Export emails</li>' +
                        '<li style="padding:8px; cursor:pointer;">Export full</li>' +
                        '</ul>' +
                        '</div>').insertBefore($('.wp-header-end'));

                    jQuery(document).ready(function ($) {
                        $('.export-dropdown button').on('click', function (e) {
                            e.preventDefault();
                            $(this).next('ul').toggle();
                        });

                        // Export emails
                        $('.export-dropdown ul li:contains("Export emails")').on('click', function () {
                            window.location.href = '<?php echo admin_url("admin-post.php?action=growtype_form_admin_export_emails"); ?>';
                        });

                        // Export full
                        $('.export-dropdown ul li:contains("Export full")').on('click', function () {
                            window.location.href = '<?php echo admin_url("admin-post.php?action=growtype_form_admin_export_leads"); ?>';
                        });

                        // Hide dropdown on click outside
                        $(document).on('click', function (e) {
                            if (!$(e.target).closest('.export-dropdown').length) {
                                $('.export-dropdown ul').hide();
                            }
                        });
                    });
                });
            </script>
            <?php
        }
    }
}
