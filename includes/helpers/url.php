<?php

/**
 * @return false|string|WP_Error|null
 * Custom profile page url
 */
function growtype_form_user_profile_url()
{
    return !empty(get_page_by_path('profile')) ? get_permalink(get_page_by_path('profile')) : null;
}

/**
 * @return false|string|WP_Error|null
 * Custom login url
 */
function growtype_form_login_page_url()
{
    return !empty(growtype_form_login_page()) ? get_permalink(growtype_form_login_page()) : null;
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_signup_page_url()
{
    return !empty(growtype_form_signup_page()) ? get_permalink(growtype_form_signup_page()) : null;
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_product_upload_page_url()
{
    return !empty(growtype_form_product_upload_page()) ? get_permalink(growtype_form_product_upload_page()) : null;
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
function growtype_form_lost_password_page_url()
{
    return wp_lostpassword_url();
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
    } else {
        $redirect_url = get_permalink($redirect_page);
    }

    return $redirect_url;
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
function growtype_form_redirect_url_after_signup()
{
    $redirect_page = growtype_form_redirect_after_signup_page();

    if (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'wp/wp-login')) {
        $redirect_url = get_dashboard_url();
    } elseif ($redirect_page === 'dashboard') {
        $redirect_url = get_dashboard_url();
    } else {
        $redirect_url = get_permalink($redirect_page);
    }

    return $redirect_url;
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
function growtype_form_redirect_url_after_product_creation()
{
    return get_option('growtype_form_redirect_after_product_creation');
}

/**
 * @param $path
 * @param $scheme
 * @return string|void
 */
function growtype_form_admin_url($path = '', $scheme = 'admin')
{
    return admin_url($path, $scheme);;
}
