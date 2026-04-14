<?php

/**
 * Handles exporting validated emails to CSV.
 */
class Growtype_Form_Admin_Lead_Export_Validated_Emails
{
    public static function run()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 'Error', ['response' => 403]);
        }

        $args = [
            'post_type'      => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'validation_status',
                    'value'   => 'valid',
                    'compare' => '='
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'newsletter_unsubscribed',
                        'value'   => '1',
                        'compare' => '!='
                    ],
                    [
                        'key'     => 'newsletter_unsubscribed',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ]
        ];

        $args = apply_filters('growtype_form_export_validated_emails_args', $args);

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            wp_die('No validated leads found.');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gf_leads_exported_validated_emails_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Nr', 'ID', 'Email', 'Date']);

        foreach ($query->posts as $index => $lead_id) {
            fputcsv($output, [
                $index + 1,
                $lead_id,
                get_the_title($lead_id),
                get_the_date('Y-m-d H:i:s', $lead_id),
            ]);
        }

        fclose($output);
        exit;
    }
}
