<?php

/**
 * @return false|mixed|void
 */
function growtype_form_login_page_ID()
{
    return get_option('growtype_form_login_page');
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_login_page_is_active()
{
    $page_ID = growtype_form_login_page_ID();

    if (isset($_SERVER['REQUEST_URI']) && $page_ID === 'default') {
        $current_url_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

        return strtok($current_url_slug, '?') === Growtype_Form_Login::URL_SLUG;
    }

    $post = get_post($page_ID);

    return !empty($post) && str_contains($_SERVER['REQUEST_URI'], $post->post_name);
}

/**
 * @return false|string|WP_Error|null
 * Custom login url
 */
function growtype_form_login_page_url()
{
    if (!empty(growtype_form_login_page_ID()) && growtype_form_login_page_ID() === 'default') {
        return home_url(Growtype_Form_Login::URL_SLUG);
    }

    return !empty(growtype_form_login_page_ID()) ? get_permalink(growtype_form_login_page_ID()) : null;
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_redirect_after_login_page()
{
    return get_option('growtype_form_redirect_after_login_page');
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
function growtype_form_redirect_url_after_login()
{
    $redirect_page = growtype_form_redirect_after_login_page();

    if (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'wp/wp-login')) {
        $redirect_url = get_dashboard_url();
    } elseif ($redirect_page === 'dashboard') {
        $redirect_url = get_dashboard_url();
    } elseif ($redirect_page === 'default-profile') {
        $redirect_url = home_url(Growtype_Form_Profile::URL_SLUG);
    } else {
        $redirect_url = get_permalink($redirect_page);
    }

    return $redirect_url;
}
