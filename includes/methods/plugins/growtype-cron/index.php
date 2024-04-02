<?php

add_filter('growtype_cron_scheduled_events', function ($events) {
    $events['growtype_cron_growtype_form_newsletter'] = [
        'job_name' => 'Growtype_Form_Newsletter_Job',
        'job_path' => __DIR__ . '/jobs/Growtype_Form_Newsletter_Job.php',
        'recurrence' => 'weekly',
    ];

    return $events;
}, 0, 1);
