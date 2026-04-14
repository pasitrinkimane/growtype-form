<?php

/**
 * Handles fetch and validation logic for gf_lead emails.
 */
class Growtype_Form_Admin_Lead_Fetch
{
    public function __construct()
    {
        add_action('wp_ajax_growtype_form_admin_fetch_emails', array ($this, 'fetch_emails_callback'));
        add_action('admin_head-edit.php', array ($this, 'render_scripts'));
        add_action('wp_loaded', array ($this, 'process_custom_bulk_actions'));
    }

    function process_custom_bulk_actions()
    {
        if (current_user_can('manage_options') && isset($_GET['post_type']) && $_GET['post_type'] === Growtype_Form_Admin_Lead::POST_TYPE_NAME && isset($_GET['lead_fetch_action']) && $_GET['lead_fetch_action'] !== '') {
            $action = $_GET['lead_fetch_action'];

            if ($action === 'fetch_emails') {
                self::fetch_emails();
            }

            wp_redirect(admin_url('edit.php?post_type=' . Growtype_Form_Admin_Lead::POST_TYPE_NAME));
            exit;
        }
    }

    function render_scripts()
    {
        global $typenow;

        if ($typenow !== Growtype_Form_Admin_Lead::POST_TYPE_NAME) {
            return;
        }
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var $actionsBar = $('.leads-actions-bar');

                var $fetchBtn = $('<a href="#" class="button button-primary">Fetch emails</a>');
                $fetchBtn.on('click', function (e) {
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
                $actionsBar.append($fetchBtn);
            });
        </script>
        <?php
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

            foreach ($existing_emails as $email) {
                unset($emails_to_insert[$email]);
            }
        }

        // 4. Insert remaining emails
        foreach ($emails_to_insert as $email) {
            wp_insert_post([
                'post_type'   => $lead_post_type,
                'post_title'  => $email,
                'post_status' => 'publish'
            ]);
        }

        return [
            'emails_inserted' => count($emails_to_insert),
            'total_fetched'   => $total_fetched,
        ];
    }

    function fetch_emails_callback()
    {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit  = isset($_POST['limit']) ? intval($_POST['limit']) : 1000;

        $fetch_emails = self::fetch_emails($limit, $offset);

        wp_send_json_success($fetch_emails);
    }
}
