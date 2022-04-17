<?php

/**
 *
 */
class Growtype_Form_Signup
{
    const CUSTOM_SLUG = 'signup';

    public function __construct()
    {
        add_action('init', array ($this, 'custom_url'), 1);
        add_action('template_redirect', array ($this, 'custom_url_template'));
        add_filter('document_title_parts', array ($this, 'custom_document_title_parts'));
    }

    /**
     * @param $title
     * @return string
     */
    function custom_document_title_parts($title_parts)
    {
        if (growtype_form_signup_page_is_active() && growtype_form_signup_page_ID() === 'default') {
            $title_parts['title'] = __('Sign up', 'growtype-form');
        }

        return $title_parts;
    }

    /**
     * @return void
     */
    function custom_url()
    {
        if (growtype_form_signup_page_ID() === 'default') {
            add_rewrite_endpoint(self::CUSTOM_SLUG, EP_ROOT);
        }
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            if (growtype_form_signup_page_is_active() && growtype_form_signup_page_ID() === 'default') {
                echo growtype_form_include_view('signup/default');
                exit;
            }
        }
    }

    /**
     * @param $user
     * @return array
     */
    public static function get_signup_data($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }

        $user_meta = get_user_meta($user_id);
        $form_name = isset($user_meta['growtype_form_name']) ? $user_meta['growtype_form_name'][0] : null;
        $json_form_encoded = get_option('growtype_form_signup_json_content');
        $json_form = json_decode($json_form_encoded, true);
        $form_data = isset($json_form[$form_name]) ? $json_form[$form_name] : $json_form['signup'];
        $main_fields = $form_data['main_fields'];

        $user_data = [];
        foreach ($main_fields as $field) {
            $field_name = $field['name'] ?? null;
            $field_type = $field['type'] ?? null;

            if ($field['type'] === 'custom') {
                continue;
            }

            if ($field_type === 'repeater') {
                foreach ($user_meta as $meta_key => $meta_value) {
                    if (str_contains($meta_key, $field_name)) {
                        $json_data = unserialize($meta_value[0]);
                        $json_data_formatted = '';
                        foreach ($json_data as $key => $value) {
                            $json_data_formatted .= $key . ' - ' . $value . ",\n";
                        }
                        $user_data[$meta_key] = [
                            'label' => $meta_key,
                            'value' => $json_data_formatted
                        ];
                    }
                }
            } else {
                $meta_value = isset($user_meta[$field_name]) ? $user_meta[$field_name][0] : null;
                if (!empty($meta_value)) {
                    $user_data[$field['name']] = [
                        'label' => $field['label'] ?? $field['name'] ?? null,
                        'value' => $meta_value
                    ];
                }
            }
        }

        return $user_data;
    }
}
