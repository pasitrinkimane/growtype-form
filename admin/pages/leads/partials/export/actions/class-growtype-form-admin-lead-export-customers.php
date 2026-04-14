<?php

/**
 * Handles exporting WooCommerce customers to CSV.
 */
class Growtype_Form_Admin_Lead_Export_Customers
{
    const EXPORT_TYPES = [
        'facebook' => [
            'filename' => 'facebook_',
            'headers'  => ['email', 'phone', 'fn', 'ln', 'zip', 'ct', 'st', 'country', 'uid', 'value']
        ],
        'xai' => [
            'filename' => 'xai_',
            'headers'  => ['email', 'first_name', 'last_name', 'phone', 'city', 'state', 'country', 'zip']
        ]
    ];

    public static function run()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 'Error', ['response' => 403]);
        }

        // Get all WooCommerce customer user IDs
        $customer_ids = get_users([
            'role'   => 'customer',
            'fields' => ['ID', 'user_email'],
        ]);

        if (empty($customer_ids)) {
            wp_die('No customers found.');
        }

        $type = $_GET['type'] ?? 'xai';
        $export_config = self::EXPORT_TYPES[$type] ?? self::EXPORT_TYPES['xai'];

        $filename = 'gf_leads_exported_customers_' . $export_config['filename'] . date('Y-m-d_H-i-s') . '.csv';
        $headers = $export_config['headers'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        foreach ($customer_ids as $customer) {
            $user_id = $customer->ID;
            
            // Try to get WooCommerce billing data first, fallback to basic user data
            $email = get_user_meta($user_id, 'billing_email', true) ?: $customer->user_email;
            $fn = get_user_meta($user_id, 'billing_first_name', true) ?: get_user_meta($user_id, 'first_name', true);
            $ln = get_user_meta($user_id, 'billing_last_name', true) ?: get_user_meta($user_id, 'last_name', true);
            $phone = get_user_meta($user_id, 'billing_phone', true);
            $ct = get_user_meta($user_id, 'billing_city', true);
            $st = get_user_meta($user_id, 'billing_state', true);
            $country = get_user_meta($user_id, 'billing_country', true);
            $zip = get_user_meta($user_id, 'billing_postcode', true);
            
            if ($type === 'facebook') {
                // Total spend for LTV (Value)
                $value = 0;
                if (function_exists('wc_get_customer_total_spent')) {
                    $value = wc_get_customer_total_spent($user_id);
                }

                fputcsv($output, [
                    $email,
                    $phone,
                    $fn,
                    $ln,
                    $zip,
                    $ct,
                    $st,
                    $country,
                    $user_id,
                    $value
                ]);
            } else {
                fputcsv($output, [
                    $email,
                    $fn,
                    $ln,
                    $phone,
                    $ct,
                    $st,
                    $country,
                    $zip
                ]);
            }
        }

        fclose($output);
        exit;
    }
}
