<?php

if (!function_exists('growtype_form_message')) {
    function growtype_form_message($key = 'general_error')
    {
        $messages = [
            'general_error' => __("Something went wrong. Please contact the website administrator.", "growtype-form"),
            'submission_throttled' => __("You're submitting too fast. Please wait a few seconds.", "growtype-form"),
        ];
        return $messages[$key] ?? $messages['general_error'];
    }
}
