<?php

if (!function_exists('growtype_form_get_lead_by_email')) {
    function growtype_form_get_lead_by_email($email, $single = true)
    {
        $leads = get_posts([
            'title' => $email,
            'post_type' => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
        ]);

        return $single ? end($leads) : $leads;
    }
}
