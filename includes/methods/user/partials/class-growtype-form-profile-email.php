<?php

/**
 *
 */
class Growtype_Form_Profile_Email extends Growtype_Form_Profile
{
    use GrowtypeFormUser;

    const URL_PATH = Growtype_Form_Profile_Home::URL_PATH . '/email';
    const EDIT_FORM_KEY = 'profile_email_form_data';

    const EDIT_PROFILE_FIELDS = [
        [
            "type" => "checkbox",
            "name" => "newsletter_unsubscribed",
            "label" => "Newsletter Unsubscribed",
            "class" => "col-12",
            "required" => false
        ],
    ];

    public function __construct()
    {
        add_filter('growtype_form_user_profile_custom_pages', function ($pages) {
            $pages[] = [
                'handler' => 'Growtype_Form_Profile_Email',
                'title' => 'Email',
                'key' => 'profile-email',
                'view_path' => 'profile.email',
                'url' => self::URL_PATH
            ];

            return $pages;
        });

        add_filter('growtype_form_user_profile_page_data', function ($data) {
            $data[self::EDIT_FORM_KEY] = self::edit_profile_form_data(self::EDIT_FORM_KEY);

            return $data;
        });

        add_filter('growtype_form_get_growtype_form_data', function ($form_data, $form_name) {
            if ($form_name === self::EDIT_FORM_KEY) {
                $form_data = self::edit_profile_form_data($form_name);
            }

            return $form_data;
        }, 0, 2);

        add_filter('growtype_form_upload_post_custom', function ($form_data, $submitted_values, $form_name) {
            if ($form_name === self::EDIT_FORM_KEY) {
                $result = parent::update_profile_form_details($form_name, $submitted_values['data'], self::EDIT_PROFILE_FIELDS);

                if (is_wp_error($result)) {
                    $form_data['success'] = false;
                    $form_data['message'] = $result->get_error_message();
                } elseif (is_array($result) && isset($result['success'])) {
                    $form_data['success'] = $result['success'];
                    if (isset($result['message'])) {
                        $form_data['message'] = $result['message'];
                    }
                } else {
                    $form_data['success'] = true;
                }
            }

            return $form_data;
        }, 0, 3);
    }

    public static function edit_profile_form_data($form_name)
    {
        $profile_fields = parent::edit_profile_form_fields($form_name, self::EDIT_PROFILE_FIELDS);
        $user_details = Growtype_Form_Profile::user_details();

        foreach ($profile_fields as $key => $profile_field) {
            $profile_field_name = isset($profile_field['name']) && isset($user_details[$profile_field['name']][0]) ? $user_details[$profile_field['name']][0] : '';

            if (!isset($profile_field) || empty($profile_field['value'])) {
                $profile_fields[$key]['value'] = $profile_field_name;
            }
        }

        return [
            "type" => "custom",
            "message" => "Profile information updated.",
            "main_fields" => $profile_fields,
            "submit_row" => [
                "cta" => [
                    [
                        "type" => "submit",
                        "label" => "Save changes",
                        "class" => "btn btn-primary mt-4 ms-auto"
                    ]
                ]
            ]
        ];
    }
}
