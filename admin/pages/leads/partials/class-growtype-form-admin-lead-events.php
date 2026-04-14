<?php

/**
 * Handles events logging for gf_lead.
 */
class Growtype_Form_Admin_Lead_Events
{
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
}
