<?php

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
    return admin_url($path, $scheme);
}

/**
 * @return string
 */
function growtype_form_get_url_path()
{
    $url_path = parse_url($_SERVER['REQUEST_URI']);
    $url_path = isset($url_path['path']) && !empty($url_path['path']) ? array_filter(explode('/', $url_path['path'])) : '';
    $url_path = !empty($url_path) ? implode('/', $url_path) : '';

    return $url_path;
}
