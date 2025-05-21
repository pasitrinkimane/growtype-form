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
    return apply_filters('growtype_form_redirect_url_after_product_creation', get_option('growtype_form_redirect_after_product_creation'));
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
    $url_path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : '';
    $url_path = isset($url_path['path']) && !empty($url_path['path']) ? array_filter(explode('/', $url_path['path'])) : '';
    $url_path = !empty($url_path) ? implode('/', $url_path) : '';

    return $url_path;
}

function growtype_form_get_current_domain()
{
    // Default to HTTP protocol if HTTPS is not set
    $protocol = 'http';

    // Check if HTTPS is set and not 'off'
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    }

    // Check if HTTP_HOST is set
    if (isset($_SERVER['HTTP_HOST'])) {
        $domain = $_SERVER['HTTP_HOST'];
    } else {
        // Return null or a default value if HTTP_HOST is not set
        return null;
    }

    // Return the full domain with protocol
    return $protocol . '://' . $domain;
}

function growtype_form_add_domain_to_url_if_missing($url)
{
    $current_domain = growtype_form_get_current_domain();

    if (!$current_domain) {
        return $url;
    }

    if (!empty($url) && strpos($url, 'http') !== 0) {
        return rtrim($current_domain, '/') . '/' . ltrim($url, '/');
    }

    return $url;
}
