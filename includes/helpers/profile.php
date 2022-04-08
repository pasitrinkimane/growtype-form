<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_profile_page_ID()
{
    return 'default';
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_profile_page_is_active()
{
    $page_ID = growtype_form_profile_page_ID();

    if (isset($_SERVER['REQUEST_URI']) && $page_ID === 'default') {
        $current_url_slug = str_replace('/', '', $_SERVER['REQUEST_URI']);

        return strtok($current_url_slug, '?') === Growtype_Form_Profile::URL_SLUG;
    }

    $post = get_post($page_ID);

    return !empty($post) && str_contains($_SERVER['REQUEST_URI'], $post->post_name);
}
