<?php

/**
 * Handles validation logic for gf_lead emails.
 */
class Growtype_Form_Admin_Lead_Validate
{
    public function __construct()
    {
        add_action('wp_ajax_growtype_form_admin_validate_emails', array ($this, 'validate_emails_callback'));
        add_action('admin_head-edit.php', array ($this, 'render_scripts'));
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

                var $validateBtn = $('<a href="#" class="button button-primary">Validate emails</a>');
                $validateBtn.on('click', function (e) {
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
                $actionsBar.append($validateBtn);
            });
        </script>
        <?php
    }

    function validate_emails_callback()
    {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit  = isset($_POST['limit']) ? intval($_POST['limit']) : 100;

        $args = [
            'post_type'      => 'gf_lead',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => 'is_validated',
                    'value'   => '1',
                    'compare' => '!=',
                ],
                [
                    'key'     => 'is_validated',
                    'compare' => 'NOT EXISTS',
                ]
            ],
            'fields' => 'ids',
        ];

        $leads_query = new WP_Query($args);

        if (!$leads_query->have_posts()) {
            wp_send_json_success([
                'emails_valid'  => 0,
                'total_fetched' => 0
            ]);
        }

        $emailsIds = $leads_query->posts;

        $validate_emails = self::validate_emails($emailsIds);

        wp_send_json_success($validate_emails);
    }

    public static function validate_emails($emailsIds)
    {
        $countValidated = 0;

        foreach ($emailsIds as $lead_id) {
            $email = get_the_title($lead_id);
            if (!$email) {
                continue;
            }

            $email_validation = Growtype_Form_Crud_Validation::basic_email_validation($email);
            $isValid          = $email_validation['success'];

            update_post_meta($lead_id, 'is_validated', true);
            update_post_meta($lead_id, 'validation_status', $isValid ? 'valid' : 'invalid');

            if ($isValid) {
                $countValidated++;
            }
        }

        return [
            'emails_valid'  => $countValidated,
            'total_fetched' => count($emailsIds)
        ];
    }
}
