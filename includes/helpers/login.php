<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_login_page()
{
    return get_option('growtype_form_login_page') ? get_post(get_option('growtype_form_login_page')) : false;
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_login_page_is_active()
{
    return growtype_form_login_page() && !empty(growtype_form_login_page()) &&
        is_page(growtype_form_login_page()->post_name);
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_redirect_after_login_page()
{
    return get_post(get_option('growtype_form_redirect_after_login_page'));
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_redirect_after_signup_page()
{
    return get_post(get_option('growtype_form_redirect_after_signup_page'));
}
