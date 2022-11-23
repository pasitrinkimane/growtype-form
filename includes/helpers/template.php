<?php

use function App\sage;

/**
 * @param $path
 * @param $data
 * @param $view_path
 * @return mixed
 */
function growtype_form_include_view($path, $data = [], $view_path = null)
{
    if (empty($view_path)) {
        $plugin_root = plugin_dir_path(dirname(__DIR__));
        $view_path = $plugin_root . 'resources/views/';
    }

    if (!function_exists('App\sage')) {
        return '';
    }

    $full_path = $view_path . str_replace('.', '/', $path) . '.blade.php';

    return sage('blade')->render($full_path, ['data' => $data]);
}

/**
 *
 */
function growtype_form_get_login_page_template()
{
    return get_option('growtype_form_login_page_template');
}

/**
 *
 */
function growtype_form_get_signup_page_template()
{
    return get_option('growtype_form_signup_page_template');
}
