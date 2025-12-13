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
function growtype_form_current_page_is_profile_page()
{
    global $wp_query;
    return isset($wp_query->query_vars[Growtype_Form_Profile_Edit::URL_PATH . '_page']) && $wp_query->query_vars[Growtype_Form_Profile_Edit::URL_PATH . '_page'] === '1';
}

function growtype_form_current_page_is_profile_edit_page()
{
    global $wp_query;
    return isset($wp_query->query_vars[Growtype_Form_Profile_Edit::URL_PATH . '_page']) && $wp_query->query_vars[Growtype_Form_Profile_Edit::URL_PATH . '_page'] === '1';
}

function growtype_form_current_page_is_onboarding_page()
{
    global $wp_query;

    $existing_steps = array_keys(Growtype_Form_Signup_Onboarding::get_steps());
    $existing_steps = array_merge($existing_steps, ['1']);

    return isset($wp_query->query_vars[Growtype_Form_Signup_Onboarding::URL_PATH . '_page']) && in_array($wp_query->query_vars[Growtype_Form_Signup_Onboarding::URL_PATH . '_page'], $existing_steps);
}

function growtype_form_current_page_is_profile_email_page()
{
    global $wp_query;
    return isset($wp_query->query_vars[Growtype_Form_Profile_Email::URL_PATH . '_page']) && $wp_query->query_vars[Growtype_Form_Profile_Email::URL_PATH . '_page'] === '1';
}

function growtype_form_current_page_is_profile_security_page()
{
    global $wp_query;
    return isset($wp_query->query_vars[Growtype_Form_Profile_Security::URL_PATH . '_page']) && $wp_query->query_vars[Growtype_Form_Profile_Security::URL_PATH . '_page'] === '1';
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
function growtype_form_current_page_is_profile_settings_page()
{
    global $wp_query;
    return isset($wp_query->query_vars[Growtype_Form_Profile_Settings::URL_PATH . '_page']) && $wp_query->query_vars[Growtype_Form_Profile_Settings::URL_PATH . '_page'] === '1';
}
