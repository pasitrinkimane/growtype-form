<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_default_product_status()
{
    return get_option('growtype_form_default_product_status');
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_default_product_catalog_visibility()
{
    return get_option('growtype_form_default_product_catalog_visibility');
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_default_product_type()
{
    return get_option('growtype_form_default_product_type');
}
