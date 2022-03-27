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
    return admin_url($path, $scheme);;
}
