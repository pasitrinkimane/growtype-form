<?php

/**
 * @param $url
 * @return false|mixed|string|WP_Error|null
 * Custom url
 */
function growtype_form_string_replace_custom_variable($url)
{
    $url_variables = [
        '$login_page_url' => growtype_form_login_page_url(),
        '$register_page_url' => growtype_form_signup_page_url(),
        '$logo_url' => growtype_get_login_logo()['url'] ?? ''
    ];

    foreach ($url_variables as $key => $variable) {
        if (strpos($url, $key) > -1) {
            $url = str_replace($key, $variable, $url);
        }
    }

    return $url;
}

/**
 * @param $form
 * @return array
 */
function growtype_form_extract_form_args($form)
{
    $type = $form['type'] ?? null;
    $logo = $form['logo'] ?? null;
    $header = $form['header'] ?? null;
    $footer = $form['footer'] ?? null;
    $confirmation_fields = isset($form['confirmation_fields']) && !empty($form['confirmation_fields']) ? $form['confirmation_fields'] : "";
    $class = isset($form['class']) && !empty($form['class']) ? $form['class'] : "";
    $submit_row = isset($form['submit_row']) && !empty($form['submit_row']) ? $form['submit_row'] : "";
    $main_fields = isset($form['main_fields']) && !empty($form['main_fields']) ? $form['main_fields'] : "";

    $wp_login_form = $form['form'] ?? null;
    $lost_password_btn = $wp_login_form['lost_password_btn'] ?? null;
    $lost_password_label = $wp_login_form['lost_password_label'] ?? null;
    $username_placeholder = $wp_login_form['username_placeholder'] ?? "";
    $password_placeholder = $wp_login_form['password_placeholder'] ?? "";
    $redirect = isset($wp_login_form['redirect']) && !empty($wp_login_form['redirect']) ? $wp_login_form['redirect'] : admin_url();
    $wp_login_form_id = isset($wp_login_form['form_id']) && !empty($wp_login_form['form_id']) ? $wp_login_form['form_id'] : "loginform-custom";
    $label_username = isset($wp_login_form['label_username']) && !empty($wp_login_form['label_username']) ? $wp_login_form['label_username'] : "";
    $label_password = isset($wp_login_form['label_password']) && !empty($wp_login_form['label_password']) ? $wp_login_form['label_password'] : "";
    $label_remember = isset($wp_login_form['label_remember']) && !empty($wp_login_form['label_remember']) ? $wp_login_form['label_remember'] : "";
    $label_log_in = isset($wp_login_form['label_log_in']) && !empty($wp_login_form['label_log_in']) ? $wp_login_form['label_log_in'] : "";
    $remember = isset($wp_login_form['remember']) && !empty($wp_login_form['remember']) ? $wp_login_form['remember'] : true;

    return [
        'type' => $type,
        'class' => $class,
        'logo' => $logo,
        'lost_password_btn' => $lost_password_btn,
        'lost_password_label' => $lost_password_label,
        'username_placeholder' => $username_placeholder,
        'password_placeholder' => $password_placeholder,
        'footer' => $footer,
        'header' => $header,
        'confirmation_fields' => $confirmation_fields,
        'submit_row' => $submit_row,
        'main_fields' => $main_fields,
        'wp_login_form' => [
            'redirect' => $redirect,
            'form_id' => $wp_login_form_id,
            'label_username' => $label_username,
            'label_password' => $label_password,
            'label_remember' => $label_remember,
            'label_log_in' => $label_log_in,
            'remember' => $remember
        ]
    ];
}
