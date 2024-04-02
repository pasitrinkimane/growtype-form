<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_profile_page_id()
{
    return 'default';
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_profile_page_is_active()
{
    $page_ID = growtype_form_profile_page_id();

    if (isset($_SERVER['REQUEST_URI']) && $page_ID === 'default') {
        $page_path = parse_url($_SERVER['REQUEST_URI']);
        $page_path = isset($page_path['path']) && !empty($page_path['path']) ? array_filter(explode('/', $page_path['path'])) : '';
        $page_path = !empty($page_path) ? implode('/', $page_path) : '';

        return $page_path === Growtype_Form_Profile::URL_PATH;
    }

    $post = get_post($page_ID);

    return !empty($post) && strpos($_SERVER['REQUEST_URI'], $post->post_name) !== false;
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_profile_settings_page_id()
{
    return 'default';
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_profile_settings_page_is_active()
{
    $page_ID = growtype_form_profile_settings_page_id();

    if (isset($_SERVER['REQUEST_URI']) && $page_ID === 'default') {
        $page_path = parse_url($_SERVER['REQUEST_URI']);
        $page_path = isset($page_path['path']) && !empty($page_path['path']) ? array_filter(explode('/', $page_path['path'])) : '';
        $page_path = !empty($page_path) ? implode('/', $page_path) : '';

        return $page_path === Growtype_Form_Profile_Settings::URL_PATH;
    }

    $post = get_post($page_ID);

    return !empty($post) && strpos($_SERVER['REQUEST_URI'], $post->post_name) !== false;
}
