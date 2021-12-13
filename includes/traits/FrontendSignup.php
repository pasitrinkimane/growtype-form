<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/partials
 */

trait FrontendSignup
{
    /**
     * @param $data
     * @return array
     */
    function save_submitted_signup_data($data)
    {
        $email = isset($data['email']) ? sanitize_text_field($data['email']) : null;
        $username = isset($data['username']) ? sanitize_text_field($data['username']) : null;
        $username = !empty($username) ? $username : $email;
        $password = isset($data['password']) ? sanitize_text_field($_REQUEST['password']) : null;
        $repeat_password = isset($data['repeat_password']) ? sanitize_text_field($_REQUEST['repeat_password']) : null;


        if (empty($username) || empty($password) || empty($email)) {
            $response['success'] = false;
            $response['message'] = __("Missing required values", "growtype-form");
            return $response;
        }

        if (!empty($repeat_password)) {
            if ($password !== $repeat_password) {
                $response['success'] = false;
                $response['message'] = __("Passwords do not match", "growtype-form");
                return $response;
            }
        }

        $validate_password = $this->validate_password($password);

        if ($validate_password['success'] === false) {
            $response['success'] = $validate_password['success'];
            $response['message'] = $validate_password['message'];
            return $response;
        }

        /**
         * Save with unique email. Check if username is provided and email already exists in database.
         */
        if ($username !== $email && email_exists($email)) {
            $email_exploded = explode('@', $email);
            $username_formatted = urlencode(str_replace(' ', '', $username));
            $email = $email_exploded[0] . '+' . $username_formatted . '@' . $email_exploded[1];
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            $response['success'] = false;
            $response['message'] = __("Profile already registered.", "growtype-form");
        } else {
            $response = $this->update_user_data($user_id, $data);

            if ($response['success']) {
                $response['message'] = __("Sign up successful.", "growtype-form");
            } else {
                $response['message'] = __("Something went wrong.", "growtype-form");
            }
        }

        return $response;
    }

    /**
     * @param $password
     * @return array
     * Validate password
     */
    function validate_password($password)
    {
        $status['success'] = true;

        if (!empty($password)) {

            $allow_simple_password = get_option('growtype_form_allow_simple_password');

            if ($allow_simple_password) {
                return $status;
            }

            if (strlen($password) <= '8') {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 8 Characters!", "growtype-registration");
            } elseif (!preg_match("#[0-9]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Number!", "growtype-registration");
            } elseif (!preg_match("#[A-Z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Capital Letter!", "growtype-registration");
            } elseif (!preg_match("#[a-z]+#", $password)) {
                $status['success'] = false;
                $status['message'] = __("Your Password Must Contain At Least 1 Lowercase Letter!", "growtype-registration");
            }
        } else {
            $status['success'] = false;
            $status['message'] = __("Please enter password.", "growtype-registration");
        }

        return $status;
    }
}


