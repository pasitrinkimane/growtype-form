<?php

/**
 * @param $url
 * @return false|mixed|string|WP_Error|null
 * Custom url
 */
function growtype_form_string_replace_custom_variable($url)
{
    $url_variables = [
        '$login_page_url' => growtype_form_login_page_url(),
        '$register_page_url' => growtype_form_signup_page_url(),
        '$logo_url' => growtype_get_login_logo()['url'] ?? ''
    ];

    foreach ($url_variables as $key => $variable) {
        if (strpos($url, $key) > -1) {
            $url = str_replace($key, $variable, $url);
        }
    }

    return $url;
}
