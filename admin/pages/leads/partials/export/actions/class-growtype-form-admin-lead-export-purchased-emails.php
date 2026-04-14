<?php

/**
 * Handles exporting purchased emails to CSV.
 */
class Growtype_Form_Admin_Lead_Export_Purchased_Emails
{
    public static function run()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 'Error', ['response' => 403]);
        }

        global $wpdb;

        $purchased_emails = $wpdb->get_col("
            SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '_billing_email' 
            AND p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-active')
        ");

        if (empty($purchased_emails)) {
            wp_die('No purchasers found.');
        }

        $purchased_emails_map = array_flip($purchased_emails);

        $args  = ['post_type' => Growtype_Form_Admin_Lead::POST_TYPE_NAME, 'posts_per_page' => -1, 'fields' => 'ids'];
        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            wp_die('No leads found.');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gf_leads_exported_purchased_emails_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Nr', 'ID', 'Email', 'Date']);

        $count = 0;
        foreach ($query->posts as $lead_id) {
            $email        = get_the_title($lead_id);
            $ignore_email = apply_filters('growtype_form_admin_export_leads_ignore_email', false, $email, $lead_id);

            if ($ignore_email) {
                continue;
            }

            if (isset($purchased_emails_map[$email])) {
                $count++;
                fputcsv($output, [
                    $count,
                    $lead_id,
                    $email,
                    get_the_date('Y-m-d H:i:s', $lead_id),
                ]);
            }
        }

        fclose($output);
        exit;
    }
}
