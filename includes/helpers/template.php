<?php

use function App\sage;

function growtype_form_include_view($path, $data = null)
{
    $plugin_root = plugin_dir_path(dirname(__DIR__));
    $full_path = $plugin_root . 'resources/views/' . str_replace('.', '/', $path) . '.blade.php';

    if (empty($data)) {
        return sage('blade')->render($full_path);
    }

    return sage('blade')->render($full_path, $data);
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
