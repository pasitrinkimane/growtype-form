<?php

/**
 *
 */
class Growtype_Form_Profile_Security extends Growtype_Form_Profile
{
    use GrowtypeFormUser;

    const URL_PATH = Growtype_Form_Profile_Home::URL_PATH . '/security';

    const EDIT_FORM_KEY = 'profile_security_form_data';

    const EDIT_PROFILE_FIELDS = [
        [
            "type" => "password",
            "name" => "current_password",
            "label" => "Current Password",
            "placeholder" => "Current Password",
            "class" => "col-12",
            "required" => true
        ],
        [
            "type" => "password",
            "name" => "new_password",
            "label" => "New Password",
            "placeholder" => "New Password",
            "class" => "col-12",
            "required" => true
        ],
        [
            "type" => "password",
            "name" => "repeat_new_password",
            "label" => "Repeat New Password",
            "placeholder" => "Repeat New Password",
            "class" => "col-12",
            "required" => true
        ],
    ];

    public function __construct()
    {
        add_filter('growtype_form_user_profile_custom_pages', function ($pages) {
            $pages[] = [
                'handler' => 'Growtype_Form_Profile_Security',
                'title' => 'Security',
                'key' => 'profile-security',
                'view_path' => 'profile.security',
                'url' => self::URL_PATH
            ];

            return $pages;
        });

        add_filter('growtype_form_user_profile_page_data', function ($data) {
            if (growtype_form_current_page_is_profile_security_page()) {
                $data[self::EDIT_FORM_KEY] = self::edit_profile_form_data(self::EDIT_FORM_KEY);
            }

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
                $result = self::update_profile_form_details($form_name, $submitted_values['data']);

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

        add_action('wp_enqueue_scripts', function () {
            if (!growtype_form_current_page_is_profile_security_page()) {
                return;
            }

            wp_enqueue_script(
                'growtype-profile-upload',
                GROWTYPE_FORM_URL_PUBLIC . '/scripts/growtype-form-profile-security.js',
                ['jquery'],
                null,
                true
            );

            /**
             * Localize script
             */
            wp_localize_script('growtype-profile-upload', 'growtypeProfileUpload', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('growtype_profile_upload'),
                'action' => 'growtype_form_profile_security_form_data',
            ]);
        });
    }

    public static function edit_profile_form_data($form_name)
    {
        $profile_fields = parent::edit_profile_form_fields($form_name, self::EDIT_PROFILE_FIELDS);
        $user_details = Growtype_Form_Profile::user_details();

        foreach ($profile_fields as $key => $profile_field) {
            $profile_name = isset($profile_field['name']) && $profile_field['name'] ? $profile_field['name'] : '';
            $profile_field_name = isset($profile_field['name']) && isset($user_details[$profile_field['name']][0]) ? $user_details[$profile_field['name']][0] : '';

            if ($profile_name === 'profile_picture') {
                $user_id = get_current_user_id();
                $profile_picture = get_user_meta($user_id, 'profile_picture', true);
                $default_avatar = get_avatar_url($user_id, ['size' => 300]);
                $current_photo = !empty($profile_picture) ? $profile_picture : $default_avatar;
                $has_custom_photo = !empty($profile_picture);

                $profile_fields[$key]['value'] = '
                <div class="col-left">
                    <div class="profile-avatar">' .
                    ($has_custom_photo ? '
                        <div class="e-close growtype-form-remove-profile-image">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.6465 3.64656C11.8417 3.4513 12.1583 3.4513 12.3535 3.64656C12.5488 3.84182 12.5488 4.15833 12.3535 4.35359L8.70703 8.00008L12.3535 11.6466L12.388 11.6843C12.5482 11.8807 12.5366 12.1705 12.3535 12.3536C12.1704 12.5367 11.8806 12.5483 11.6842 12.3881L11.6465 12.3536L8 8.70711L4.35352 12.3536C4.15826 12.5489 3.84175 12.5489 3.64649 12.3536C3.45122 12.1583 3.45122 11.8418 3.64649 11.6466L7.29297 8.00008L3.64649 4.35359L3.61198 4.31583C3.45177 4.11945 3.46342 3.82963 3.64649 3.64656C3.82956 3.46349 4.11937 3.45185 4.31576 3.61206L4.35352 3.64656L8 7.29305L11.6465 3.64656Z" fill="#0D0D0D"/>
                            </svg>
                        </div>' : '') . '
                        <div style="background-image: url(\'' . esc_url($current_photo) . '\');background-size: cover;background-position: center;background-repeat: no-repeat;" class="profile-picture"></div>
                        <input
                            type="file"
                            id="profilePhotoUpload"
                            name="profile_picture"
                            accept="image/jpeg,image/png"
                        />
                        <div class="e-label" id="uploadPhotoBtn">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.25 5C15.25 4.9337 15.2236 4.87013 15.1768 4.82324C15.1299 4.77636 15.0663 4.75 15 4.75H9C8.9337 4.75 8.87013 4.77636 8.82324 4.82324C8.77636 4.87013 8.75 4.9337 8.75 5C8.75 5.72935 8.46006 6.42861 7.94434 6.94434C7.42861 7.46006 6.72935 7.75 6 7.75H5C4.66848 7.75 4.35063 7.88179 4.11621 8.11621C3.88179 8.35063 3.75 8.66848 3.75 9V18C3.75 18.3315 3.88179 18.6494 4.11621 18.8838C4.35063 19.1182 4.66848 19.25 5 19.25H19C19.3315 19.25 19.6494 19.1182 19.8838 18.8838C20.1182 18.6494 20.25 18.3315 20.25 18V9C20.25 8.66848 20.1182 8.35063 19.8838 8.11621C19.6494 7.88179 19.3315 7.75 19 7.75H18C17.2707 7.75 16.5714 7.46006 16.0557 6.94434C15.5399 6.42861 15.25 5.72935 15.25 5ZM14.25 13C14.25 12.4033 14.0128 11.8311 13.5908 11.4092C13.1689 10.9872 12.5967 10.75 12 10.75C11.4033 10.75 10.8311 10.9872 10.4092 11.4092C9.98722 11.8311 9.75 12.4033 9.75 13C9.75 13.5967 9.98722 14.1689 10.4092 14.5908C10.8311 15.0128 11.4033 15.25 12 15.25C12.5967 15.25 13.1689 15.0128 13.5908 14.5908C14.0128 14.1689 14.25 14.1689 14.25 13Z" fill="currentColor"/>
                            </svg>
                            <label>' . ($has_custom_photo ? 'Change photo' : 'Upload photo') . '</label>
                        </div>
                    </div>
                </div>
                ';
            } elseif ($profile_name === 'country') {
                $upload_dir = wp_upload_dir();
                $dir = $upload_dir['basedir'] . '/growtype-form';
                $file = $dir . '/countries.json';

                if (!file_exists($dir)) {
                    wp_mkdir_p($dir); // recursively creates the folder
                }

                if (file_exists($file)) {
                    $country_names = json_decode(file_get_contents($file), true);
                } else {
                    // Fetch countries from RestCountries API
                    $response = file_get_contents('https://restcountries.com/v3.1/all?fields=name,languages,cca2');

                    if ($response !== false) {
                        $countries = json_decode($response, true);

                        $country_names = [];
                        foreach ($countries as $country) {
                            $country_names[$country['cca2']] = $country['name']['common'];
                        }

                        file_put_contents($file, json_encode($country_names, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }
                }

                $countries_collected = [];
                foreach ($country_names as $country_code => $country_name) {
                    $countries_collected[] = [
                        'value' => $country_code,
                        'label' => $country_name
                    ];
                }

                $profile_fields[$key]['options'] = $countries_collected;
                $profile_fields[$key]['value'] = $profile_field_name;
            } else {
                if (!isset($profile_field) || empty($profile_field['value'])) {
                    $profile_fields[$key]['value'] = $profile_field_name;
                }
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

    public static function update_profile_form_details($form_name, $submitted_values, $profile_fields = [])
    {
        $user_id = get_current_user_id();
        $user = get_user_by('id', $user_id);

        if (!$user) {
            return new WP_Error('user_not_found', 'User not found.');
        }

        $current_password = isset($submitted_values['current_password']) ? $submitted_values['current_password'] : '';
        $new_password = isset($submitted_values['new_password']) ? $submitted_values['new_password'] : '';
        $repeat_new_password = isset($submitted_values['repeat_new_password']) ? $submitted_values['repeat_new_password'] : '';

        if (empty($current_password) || empty($new_password) || empty($repeat_new_password)) {
            return new WP_Error('missing_fields', 'Please fill in all fields.');
        }

        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            return new WP_Error('incorrect_password', 'The current password you entered is incorrect.');
        }

        if ($new_password !== $repeat_new_password) {
            return new WP_Error('password_mismatch', 'New passwords do not match.');
        }

        wp_set_password($new_password, $user_id);

        // Re-login the user after password change
        $creds = [
            'user_login' => $user->user_login,
            'user_password' => $new_password,
            'remember' => true
        ];

        $signon = wp_signon($creds, false);

        if (is_wp_error($signon)) {
            return new WP_Error('login_failed', 'Password changed, but failed to re-login.');
        }

        return [
            'success' => true,
            'message' => 'Password updated successfully.'
        ];
    }
}
