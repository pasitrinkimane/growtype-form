<?php

/**
 * @param $url
 * @return false|mixed|string|WP_Error|null
 * Custom url
 */
if (!function_exists('growtype_form_string_replace_custom_variable')) {
    function growtype_form_string_replace_custom_variable($string, $params = [])
    {
        global $wp;

        $variables = [
            '$login_page_url',
            '$register_page_url',
            '$logo_url',
            '$btn_facebook_login',
            '$btn_google_login',
            '$home_url',
        ];

        $variable_to_replace = '';
        foreach ($variables as $variable) {
            if (strpos($string, $variable) !== false) {
                $variable_to_replace = $variable;
                break;
            }
        }

        if (!empty($variable_to_replace)) {

            $query_args = [];

            if (isset($_SERVER['HTTPS']) && isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
                $full_url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $parts = parse_url($full_url);
                $query_args = isset($parts['query']) ? parse_str($parts['query'], $query_args) : [];
            }

            switch ($variable_to_replace) {
                case '$login_page_url':
                    $replace = growtype_form_login_page_url($query_args);
                    break;
                case '$register_page_url':
                    $replace = growtype_form_signup_page_url($query_args);
                    break;
                case '$logo_url':
                    $replace = isset(growtype_get_login_logo()['url']) ? growtype_get_login_logo()['url'] : '';
                    break;
                case '$btn_facebook_login':
                    $replace = growtype_form_facebook_login_btn();
                    break;
                case '$btn_google_login':
                    $replace = growtype_form_google_login_btn();
                    break;
                case '$home_url':
                    $replace = home_url();
                    break;
            }

            $counter = 0;
            foreach ($params as $key => $param) {
                if ($counter === 0) {
                    $replace = $replace . '?' . $key . '=' . $param;
                } else {
                    $replace = $replace . '&' . $key . '=' . $param;
                }
                $counter++;
            }

            if (isset($replace) && !empty($replace)) {
                $string = str_replace($variable_to_replace, $replace, $string);
            }
        }

        return $string;
    }
}

/**
 * @param $form
 * @return array
 */
function growtype_form_extract_form_args($form)
{
    $type = $form['type'] ?? null;
    $ajax = $form['ajax'] ?? 'false';
    $ajax_action = $form['ajax_action'] ?? '';
    $logo = $form['logo'] ?? null;
    $header = $form['header'] ?? null;
    $footer = $form['footer'] ?? null;
    $confirmation_fields = isset($form['confirmation_fields']) && !empty($form['confirmation_fields']) ? $form['confirmation_fields'] : "";
    $class = isset($form['class']) && !empty($form['class']) ? $form['class'] : "";
    $submit_row = isset($form['submit_row']) && !empty($form['submit_row']) ? $form['submit_row'] : "";
    $main_fields = isset($form['main_fields']) && !empty($form['main_fields']) ? $form['main_fields'] : "";
    $redirect_after = isset($form['args']['redirect_after']) && !empty($form['args']['redirect_after']) ? $form['args']['redirect_after'] : "";

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
        'ajax' => $ajax,
        'ajax_action' => $ajax_action,
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
        'redirect_after' => $redirect_after,
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

/**
 * @param $form_args
 * @return mixed|null
 */
function growtype_form_fill_form_args_with_existing_data($form_args)
{
    $user_main_fields = Growtype_Form_Signup::get_signup_data(get_current_user_id());

    if (isset($form_args['main_fields'])) {
        foreach ($form_args['main_fields'] as $key => $field) {
            if (isset($form_args['main_fields'][$key]) && isset($user_main_fields[$field['name']])) {
                if ($field['type'] === 'select') {
                    $form_args['main_fields'][$key]['selected_options'] = [$user_main_fields[$field['name']]['value']];
                } else {
                    $form_args['main_fields'][$key]['value'] = $user_main_fields[$field['name']]['value'];
                }
            }
        }
    }

    if (isset($form_args['confirmation_fields'])) {
        foreach ($form_args['confirmation_fields'] as $key => $field) {
            if (isset($form_args['confirmation_fields'][$key]) && isset($user_main_fields[$field['name']])) {
                $form_args['confirmation_fields'][$key]['selected_options'] = [$user_main_fields[$field['name']]['value']];
            }
        }
    }

    return apply_filters('growtype_form_fill_form_args_with_existing_data', $form_args);
}

/**
 * @param $form_name
 * @param $form_field
 * @return mixed|null
 */
function growtype_form_get_form_field($form_name, $form_field)
{
    $form_data = Growtype_Form_Crud::get_growtype_form_data($form_name);

    foreach ($form_data['main_fields'] as $field) {
        if ($field['name'] === $form_field) {
            return $field;
        }
    }

    foreach ($form_data['confirmation_fields'] as $field) {
        if ($field['name'] === $form_field) {
            return $field;
        }
    }

    return null;
}
