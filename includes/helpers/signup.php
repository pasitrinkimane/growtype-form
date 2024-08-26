<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_signup_page_ID()
{
    return get_option('growtype_form_signup_page');
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_signup_page_is_active()
{
    $page_ID = growtype_form_signup_page_ID();

    if (isset($_SERVER['REQUEST_URI']) && $page_ID === 'default') {
        $current_url_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

        return strtok($current_url_slug, '?') === Growtype_Form_Signup::URL_PATH;
    }

    $post = get_post($page_ID);

    return !empty($post) && !empty($post->post_name) && strpos($_SERVER['REQUEST_URI'], $post->post_name) !== false;
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_signup_page_url($query_vars = [])
{
    $url = !empty(growtype_form_signup_page_ID()) ? get_permalink(growtype_form_signup_page_ID()) : null;

    if (!empty(growtype_form_signup_page_ID()) && growtype_form_signup_page_ID() === 'default') {
        $url = home_url(Growtype_Form_Signup::URL_PATH);
    }

    if (!empty($query_vars)) {
        $url .= '?' . http_build_query($query_vars);
    }

    return $url;
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_profile_page_url()
{
    return home_url(Growtype_Form_Profile::URL_PATH);
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_profile_settings_page_url()
{
    return home_url(Growtype_Form_Profile_Settings::URL_PATH);
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_default_redirect_after_signup_page()
{
    return get_option('growtype_form_redirect_after_signup_page');
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
if (!function_exists('growtype_form_redirect_url_after_signup')) {
    function growtype_form_redirect_url_after_signup()
    {
        $redirect_page = growtype_form_default_redirect_after_signup_page();

        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wp/wp-login') !== false) {
            $redirect_url = get_dashboard_url();
        } elseif (isset($_COOKIE['growtype_form_redirect_after'])) {
            $redirect_url = $_COOKIE['growtype_form_redirect_after'];
        } elseif ($redirect_page === 'dashboard') {
            $redirect_url = get_dashboard_url();
        } elseif ($redirect_page === 'default-profile') {
            $redirect_url = growtype_form_profile_page_url();
        } elseif ($redirect_page === 'none') {
            $redirect_url = growtype_form_profile_page_url();
        } else {
            $redirect_url = get_permalink($redirect_page);
        }

        if (empty($redirect_url) || !$redirect_url) {
            error_log('Redirect url is missing. Growtype-form');
        }

        return apply_filters('growtype_form_redirect_url_after_signup', $redirect_url);
    }
}
