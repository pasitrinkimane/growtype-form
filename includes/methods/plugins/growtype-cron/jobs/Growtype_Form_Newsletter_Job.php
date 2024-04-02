<?php

class Growtype_Form_Newsletter_Job
{
    public function run($job)
    {
        if (has_filter('growtype_form_growtype_cron_newsletter_send')) {
            $args = array (
                'post_type' => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
                'posts_per_page' => -1,
                'post_status' => 'any',
            );

            $leads = get_posts($args);

            foreach ($leads as $lead) {
                $newsletter_unsubscribed = get_post_meta($lead->ID, 'newsletter_unsubscribed', true);

                if (!empty($newsletter_unsubscribed) && $newsletter_unsubscribed == true) {
                    continue;
                }

                $data = [
                    'receiver_email' => $lead->post_title,
                    'post_id' => $lead->ID,
                ];

                $response = apply_filters('growtype_form_growtype_cron_newsletter_send', $data);

                if (isset($response['status'])) {
                    Growtype_Form_Admin_Lead::update_events_log($lead->ID, [
                        'data' => $data,
                        'success' => $response['status'] ? true : false,
                    ]);
                }
            }
        } else {
            error_log('Growtype_Form_Newsletter_Job. No filter found.');
        }
    }
}
