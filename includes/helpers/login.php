<?php

/**
 * @return false|mixed|void
 */
function growtype_form_login_page_ID()
{
    return get_option('growtype_form_login_page');
}

/**
 * @return false|mixed|void
 */
if (!function_exists('growtype_form_login_user')) {
    function growtype_form_login_user($user_id)
    {
        $user = get_user_by('id', $user_id);

        wp_set_current_user($user->ID, $user->user_login);

        wp_set_auth_cookie($user->ID);

        do_action('wp_login', $user->user_login, $user);

        error_log('User logged in: ' . $user->user_login);
    }
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_login_page_is_active()
{
    $page_ID = growtype_form_login_page_ID();

    if (isset($_SERVER['REQUEST_URI']) && $page_ID === 'default') {
        $current_url_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

        return strtok($current_url_slug, '?') === Growtype_Form_Login::URL_PATH;
    }

    $post = get_post($page_ID);

    return !empty($post) && !empty($post->post_name) && strpos($_SERVER['REQUEST_URI'], $post->post_name) !== false;
}

/**
 * @return false|string|WP_Error|null
 * Custom login url
 */
function growtype_form_login_page_url($query_vars = [])
{
    $url = !empty(growtype_form_login_page_ID()) ? get_permalink(growtype_form_login_page_ID()) : null;

    if (!empty(growtype_form_login_page_ID()) && growtype_form_login_page_ID() === 'default') {
        $url = home_url(Growtype_Form_Login::URL_PATH);
    }

    if (!empty($query_vars)) {
        $url .= '?' . http_build_query($query_vars);
    }

    return $url;
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_default_redirect_after_login_page()
{
    return get_option('growtype_form_redirect_after_login_page');
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
function growtype_form_redirect_url_after_login()
{
    $redirect_page = growtype_form_default_redirect_after_login_page();

    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wp/wp-login') !== false) {
        $redirect_url = get_dashboard_url();
    } elseif (isset($_COOKIE['growtype_form_redirect_after'])) {
        $redirect_url = $_COOKIE['growtype_form_redirect_after'];
    } elseif ($redirect_page === 'dashboard') {
        $redirect_url = get_dashboard_url();
    } elseif ($redirect_page === 'default-profile') {
        $redirect_url = home_url(Growtype_Form_Profile::URL_PATH);
    } else {
        $redirect_url = get_permalink($redirect_page);
    }

    return apply_filters('growtype_form_redirect_url_after_login', $redirect_url);
}

if (!function_exists('growtype_form_current_page_is_login_page')) {
    function growtype_form_current_page_is_login_page()
    {
        return strpos($_SERVER['REQUEST_URI'], Growtype_Form_Login::URL_PATH) !== false;
    }
}

if (!function_exists('growtype_form_current_page_is_signup_page')) {
    function growtype_form_current_page_is_signup_page()
    {
        return strpos($_SERVER['REQUEST_URI'], Growtype_Form_Signup::URL_PATH) !== false;
    }
}
