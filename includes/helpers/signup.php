<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_signup_page()
{
    return get_option('growtype_form_signup_page') ? get_post(get_option('growtype_form_signup_page')) : false;
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_signup_page_is_active()
{
    return !empty(growtype_form_signup_page()) &&
        is_page(growtype_form_signup_page()->post_name);
}
