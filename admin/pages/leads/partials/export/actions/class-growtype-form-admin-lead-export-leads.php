<?php

/**
 * Handles exporting all lead data to CSV.
 */
class Growtype_Form_Admin_Lead_Export_Leads
{
    public static function run()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 'Error', ['response' => 403]);
        }

        $args = [
            'post_type'      => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
            'posts_per_page' => -1,
            'fields'         => 'ids'
        ];

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            wp_die('No leads found.');
        }

        $fields = [
            'nr'    => 'Nr',
            'id'    => 'ID',
            'email' => 'Email',
            'date'  => 'Date'
        ];

        foreach (Growtype_Form_Admin_Lead_Meta_Boxes::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                $fields[$field['key']] = $field['title'];
            }
        }

        $fields['is_validated']           = 'Is Validated';
        $fields['validation_status']      = 'Validation Status';
        $fields['newsletter_unsubscribed'] = 'Unsubscribed';

        $fields = apply_filters('growtype_form_export_leads_fields', $fields);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gf_leads_exported_leads_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_values($fields));

        foreach ($query->posts as $index => $lead_id) {
            $row   = [];
            $email = get_the_title($lead_id);
            $user  = get_user_by('email', $email);

            foreach ($fields as $key => $label) {
                if ($key === 'nr') {
                    $row[] = $index + 1;
                } elseif ($key === 'id') {
                    $row[] = $lead_id;
                } elseif ($key === 'email') {
                    $row[] = $email;
                } elseif ($key === 'date') {
                    $row[] = get_the_date('Y-m-d H:i:s', $lead_id);
                } else {
                    $value = get_post_meta($lead_id, $key, true);
                    if (($value === '' || $value === null) && !empty($user)) {
                        $value = get_user_meta($user->ID, $key, true);
                    }
                    $row[] = apply_filters('growtype_form_export_leads_row_value', $value, $key, $lead_id, $user);
                }
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
