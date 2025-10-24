<?php

/**
 *
 */
class Growtype_Form_Admin_Lead_Custom_Actions
{
    const POST_TYPE_NAME = Growtype_Form_Admin_Lead::POST_TYPE_NAME;

    public function __construct()
    {
        add_action('manage_posts_extra_tablenav', array ($this, 'bulk_actions_html'));

        add_action('init', array ($this, 'process_bulk_actions'), 20);

        /**
         * Bulk actions scripts
         */
        add_action('admin_head-edit.php', array ($this, 'bulk_actions_scripts'));
        add_action('admin_head-post.php', array ($this, 'bulk_actions_scripts'));

        /**
         * Process admin actions
         */
        add_action('admin_init', array ($this, 'process_admin_actions'));

        /**
         * Add custom action to user row
         */
        add_filter('post_row_actions', array ($this, 'user_row_actions_add_custom_actions'), 10, 2);

        /**
         * Meta boxes
         */
        add_filter('growtype_form_lead_meta_boxes', array ($this, 'get_meta_boxes'));

        /**
         *
         */
        add_action('add_meta_boxes', array ($this, 'custom_actions_meta_box'), 0, 2);
    }

    function custom_actions_meta_box($post_type, $post)
    {
        $screens = array (self::POST_TYPE_NAME);

        foreach ($screens as $screen) {
            add_meta_box(
                'growtype-form-lead-custom-actions-meta-box',
                __('Custom actions', 'growtype-form'),
                function () {
                    ?>
                    <div style="display: inline-block;width: 100%;margin-top: 10px;">
                        <?php $this->bulk_actions_html() ?>
                    </div>
                    <?php
                },
                $screen,
                'side'
            );
        }
    }

    function bulk_actions_scripts()
    {
        global $typenow;

        if ($typenow === self::POST_TYPE_NAME) { ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('#lead_custom_action_select').change(function () {
                        $('input[name="lead_custom_action"]').val($(this).val());
                    });
                });
            </script>
            <?php
        }
    }

    public function bulk_actions_html()
    {
        global $typenow;

        if ($typenow === self::POST_TYPE_NAME) { ?>
            <div class="alignleft actions bulkactions" style="display: inline-block;width: 100%;">
                <label for="lead_custom_action_select" class="screen-reader-text">Custom actions</label>
                <select id="lead_custom_action_select">
                    <option value="">Default</option>
                    <?php do_action('growtype_form_admin_lead_custom_actions_select_options'); ?>
                </select>
                <input type="hidden" name="lead_custom_action">
                <div style="margin-top: 10px;">
                    <?php echo submit_button(__('Submit'), 'action', '', false, array ('id' => "custom_action_submit")); ?>
                </div>
            </div>
            <?php
        }
    }

    function process_bulk_actions()
    {
        if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === self::POST_TYPE_NAME && isset($_REQUEST['lead_custom_action']) && $_REQUEST['lead_custom_action'] !== '') {
            $lead_custom_action = $_REQUEST['lead_custom_action'];
            $posts = $_REQUEST['post'] ?? [$_REQUEST['post_ID'] ?? ''];

            if (isset($_REQUEST['post_ID'])) {
                $url = get_edit_post_link($_REQUEST['post_ID']);
                $url = html_entity_decode($url);
            } else {
                $url = admin_url('edit.php?post_type=' . self::POST_TYPE_NAME);
            }

            if (!empty($posts)) {
                $responses = apply_filters('growtype_form_admin_lead_custom_action_submit', [
                    'action' => $lead_custom_action,
                    'ids' => $posts
                ]);
            }

            foreach ($responses as $response) {
                Growtype_Form_Admin::update_notices([
                    [
                        'message' => $response['message'] ?? '',
                        'success' => $response['success'] ?? true
                    ]
                ]);
            }

            wp_redirect($url);

            exit;
        }
    }

    function process_admin_actions()
    {
        if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === self::POST_TYPE_NAME && isset($_REQUEST['action']) && $_REQUEST['action'] === 'send_newsletter') {
            $post_id = isset($_REQUEST['post']) ? $_REQUEST['post'] : '';

            if (!empty($post_id)) {
                $post = get_post($post_id);
                $email = $post->post_title;

                if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
                    do_action('growtype_form_admin_send_newsletter', [
                        'receiver_email' => $email,
                        'post_id' => $post_id,
                        'action' => $_REQUEST['action'],
                    ]);
                }
            }

            wp_redirect(admin_url('edit.php?post_type=' . self::POST_TYPE_NAME . '&paged=' . ($_GET['paged'] ?? '')));
            exit();
        }
    }

    public static function get_meta_boxes($meta_boxes)
    {
        if (get_post_type() === 'gf_lead') {
            $meta_boxes[0]['fields'][] = [
                'title' => 'Unsubscribed',
                'key' => 'newsletter_unsubscribed',
                'type' => 'checkbox',
            ];

            $meta_boxes[0]['fields'][] = [
                'title' => 'Auth Method',
                'key' => 'auth_method'
            ];

            $meta_boxes[0]['fields'][] = [
                'title' => 'Events log',
                'key' => 'events_log',
                'type' => 'textarea',
            ];

            if (Growtype_Form_Signup_Verification::email_verification_is_required()) {
                $meta_boxes[0]['fields'][] = [
                    'title' => 'Is verified',
                    'key' => 'user_is_verified',
                ];
            }
        }

        return $meta_boxes;
    }

    function user_row_actions_add_custom_actions($actions, $post)
    {
        if ($post->post_type === self::POST_TYPE_NAME) {
            $url = add_query_arg(array (
                'action' => 'send_newsletter',
                'post' => $post->ID,
            ), admin_url('edit.php?post_type=' . self::POST_TYPE_NAME));

            $actions['send_newsletter'] = '<a href="' . $url . '">Send newsletter</a>';
        }

        return $actions;
    }
}
