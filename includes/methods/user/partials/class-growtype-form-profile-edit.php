<?php

class Growtype_Form_Profile_Edit extends Growtype_Form_Profile
{
    use GrowtypeFormUser;

    const URL_PATH = Growtype_Form_Profile_Home::URL_PATH . '/edit';
    const EDIT_FORM_KEY = 'profile_edit_form_data';

    const EDIT_PROFILE_FIELDS = [
        [
            "type" => "fully_custom",
            "name" => "profile_picture",
            "value" => '',
        ],
        [
            "type" => "fully_custom",
            "value" => "<div class='col-right'>",
        ],
        [
            "type" => "text",
            "name" => "first_name",
            "label" => "First Name",
            "placeholder" => "First Name",
            "class" => "col-12 col-md-6",
            "required" => true
        ],
        [
            "type" => "text",
            "name" => "last_name",
            "label" => "Last Name",
            "placeholder" => "Last Name",
            "class" => "col-12 col-md-6",
            "required" => true
        ],
        [
            "type" => "text",
            "name" => "user_email",
            "label" => "Email",
            "placeholder" => "Email",
            "class" => "col-12",
            "required" => true
        ],
        [
            "type" => "select",
            "name" => "country",
            "label" => "Country",
            "placeholder" => "Country",
            "class" => "col-12",
            "options" => [],
            "required" => false,
        ],
        [
            "type" => "tel",
            "name" => "phone",
            "label" => "Phone",
            "placeholder" => "Phone",
            "class" => "col-12",
            "required" => false
        ],
        [
            "type" => "hidden",
            "name" => "phone_country_iso",
        ],
        [
            "type" => "hidden",
            "name" => "phone_country_code",
        ],
        [
            "type" => "fully_custom",
            "value" => "</div>",
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        add_filter('growtype_form_user_profile_custom_pages', function ($pages) {
            $pages[] = [
                'handler' => 'Growtype_Form_Profile_Edit',
                'title' => 'Edit Profile',
                'key' => 'profile-edit',
                'view_path' => 'profile.edit',
                'url' => self::URL_PATH
            ];

            return $pages;
        });

        add_filter('growtype_form_user_profile_page_data', function ($data) {
            if (growtype_form_current_page_is_profile_edit_page()) {
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
                $result = self::update_profile_form_details($form_name, $submitted_values['data'], self::EDIT_PROFILE_FIELDS);

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

        // Add nonce field to the form
        add_filter('growtype_form_before_fields', function ($form_name) {
            if ($form_name === self::EDIT_FORM_KEY) {
                return wp_nonce_field('update_profile_picture', '_wpnonce', true, false);
            }
            return '';
        });

        add_action('wp_ajax_growtype_form_remove_profile_picture', [$this, 'remove_profile_picture']);

        add_action('wp_ajax_growtype_form_profile_edit_form_data', [$this, 'ajax_edit_profile_form_data']);

        add_action('wp_enqueue_scripts', function () {
            if (!growtype_form_current_page_is_profile_edit_page() && !growtype_form_current_page_is_onboarding_page()) {
                return;
            }

            wp_enqueue_script(
                'growtype-profile-upload',
                GROWTYPE_FORM_URL_PUBLIC . '/scripts/growtype-form-profile-edit.js',
                ['jquery'],
                null,
                true
            );

            /**
             * Localize script
             */
            wp_localize_script('growtype-profile-upload', 'growtypeProfileUpload', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'initialCountry' => get_user_meta(get_current_user_id(), 'phone_country_iso', true),
                'nonce' => wp_create_nonce('growtype_profile_upload'),
                'action' => 'growtype_form_profile_edit_form_data',
            ]);

            /**
             * Tel input library
             */
            // 2. Enqueue intl-tel-input CSS
            wp_enqueue_style(
                'intl-tel-input',
                'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.8/build/css/intlTelInput.css',
                [],
                '23.0.8'
            );

            // 3. Enqueue intl-tel-input JS + utils (needed for formatting/validation)
            wp_enqueue_script(
                'intl-tel-input-utils',
                'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.8/build/js/utils.js',
                [],
                '23.0.8',
                true
            );

            // 4. Enqueue main intl-tel-input script
            wp_enqueue_script(
                'intl-tel-input',
                'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.8/build/js/intlTelInput.min.js',
                ['intl-tel-input-utils'],
                '23.0.8',
                true
            );
        });

        add_action('growtype_form_delete', [$this, 'handle_delete_user']);
        add_action('wp_footer', [$this, 'render_delete_confirmation_script']);
    }

    public function handle_delete_user($post_id)
    {
        if (isset($_POST['growtype_form_name']) && $_POST['growtype_form_name'] === self::EDIT_FORM_KEY) {
            if (!apply_filters('growtype_form_delete_user_account', true)) {
                return;
            }

            if (!function_exists('wp_delete_user')) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }
            wp_delete_user(get_current_user_id());
            wp_redirect('https://form.typeform.com/to/sdTUaaRS');
            exit;
        }
    }

    public function render_delete_confirmation_script()
    {
        if (!growtype_form_current_page_is_profile_edit_page()) {
            return;
        }

        if (!apply_filters('growtype_form_profile_delete_js_enabled', true)) {
            return;
        }
        ?>
        <script>
            document.addEventListener('click', function (e) {
                var target = e.target.closest('button[data-action="delete"]');
                if (target) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    if (confirm('Are you sure you want to delete your account?')) {
                        var form = target.closest('form');
                        if (form) {
                            var actionInput = form.querySelector('input[name="growtype_form_submit_action"]');
                            if (!actionInput) {
                                actionInput = document.createElement('input');
                                actionInput.type = 'hidden';
                                actionInput.name = 'growtype_form_submit_action';
                                form.appendChild(actionInput);
                            }
                            actionInput.value = 'delete';
                            form.submit();
                        }
                    }
                }
            }, true);
        </script>
        <?php
    }

    public function remove_profile_picture()
    {
        // SECURITY: Verify nonce to prevent CSRF attacks
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '');
        if (empty($nonce) || !wp_verify_nonce($nonce, 'growtype_profile_upload')) {
            error_log('Growtype Form - Remove profile picture nonce verification failed');
            wp_send_json_error(['success' => false, 'message' => 'Security verification failed.'], 403);
        }
        
        $user_id = get_current_user_id();
        
        // SECURITY: Verify user is logged in
        if (!$user_id) {
            wp_send_json_error(['success' => false, 'message' => 'Authentication required.'], 401);
        }
        
        // SECURITY: Verify user can edit their own profile
        if (!current_user_can('edit_user', $user_id)) {
            wp_send_json_error(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $profile_picture_url = get_user_meta($user_id, 'profile_picture', true);
        
        if ($profile_picture_url) {
            $uploads = wp_upload_dir();
            
            // SECURITY: Validate the URL belongs to uploads directory
            if (strpos($profile_picture_url, $uploads['baseurl']) !== false) {
                $profile_picture_path = str_replace($uploads['baseurl'], $uploads['basedir'], $profile_picture_url);
                
                // SECURITY: Prevent path traversal attacks
                $real_path = realpath($profile_picture_path);
                $uploads_real_path = realpath($uploads['basedir']);
                
                if ($real_path && $uploads_real_path && strpos($real_path, $uploads_real_path) === 0) {
                    if (file_exists($real_path)) {
                        // SECURITY: Use WordPress function instead of unlink()
                        wp_delete_file($real_path);
                    }
                } else {
                    error_log('Growtype Form - Attempted path traversal: ' . $profile_picture_path);
                    wp_send_json_error(['success' => false, 'message' => 'Invalid file path.'], 400);
                }
            }
        }

        delete_user_meta($user_id, 'profile_picture');
        wp_send_json_success(['success' => true, 'message' => 'Profile picture removed successfully.']);
    }

    public function ajax_edit_profile_form_data()
    {
        // SECURITY: Verify nonce to prevent CSRF attacks
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '');
        if (empty($nonce) || !wp_verify_nonce($nonce, 'growtype_profile_upload')) {
            error_log('Growtype Form - Edit profile form data nonce verification failed');
            wp_send_json_error(['success' => false, 'message' => 'Security verification failed.'], 403);
        }
        
        if (!current_user_can('edit_user', get_current_user_id())) {
            wp_send_json_error(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        // SECURITY: Sanitize POST data
        $sanitized_post = [];
        foreach ($_POST as $key => $value) {
            if ($key === 'nonce') {
                continue; // Skip nonce field
            }
            
            if (is_array($value)) {
                $sanitized_post[$key] = array_map('sanitize_text_field', $value);
            } else {
                // Use appropriate sanitization based on field
                if ($key === 'user_email') {
                    $sanitized_post[$key] = sanitize_email($value);
                } elseif ($key === 'phone') {
                    $sanitized_post[$key] = sanitize_text_field($value);
                } else {
                    $sanitized_post[$key] = sanitize_text_field($value);
                }
            }
        }
        
        // SECURITY: Validate and sanitize file uploads
        $validated_files = [];
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                // Support both profile_picture and profile_picture[] keys
                if (in_array($key, ['profile_picture', 'profile_picture[]']) || strpos($key, 'profile_picture') !== false) {
                    
                    // If multiple files are uploaded (PHP array structure), take the first one
                    if (is_array($file['name'])) {
                        $temp_file = [];
                        foreach ($file as $property => $values) {
                            $temp_file[$property] = $values[0];
                        }
                        $file = $temp_file;
                    }

                    if (empty($file['name'])) {
                        continue;
                    }

                    // Check for upload errors
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        wp_send_json_error([
                            'success' => false,
                            'message' => 'File upload error. Please try again.'
                        ], 400);
                    }
                    
                    // SECURITY: Validate file type
                    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                    $file_info = wp_check_filetype($file['name']);
                    
                    if (!in_array($file['type'], $allowed_types) || !in_array($file_info['type'], $allowed_types)) {
                        wp_send_json_error([
                            'success' => false,
                            'message' => 'Invalid file type. Only JPEG and PNG images are allowed.'
                        ], 400);
                    }
                    
                    // SECURITY: Validate file size (max 5MB)
                    $max_size = 5 * 1024 * 1024; // 5MB in bytes
                    if ($file['size'] > $max_size) {
                        wp_send_json_error([
                            'success' => false,
                            'message' => 'File too large. Maximum file size is 5MB.'
                        ], 400);
                    }
                    
                    // SECURITY: Validate image dimensions (optional but recommended)
                    $image_info = getimagesize($file['tmp_name']);
                    if ($image_info === false) {
                        wp_send_json_error([
                            'success' => false,
                            'message' => 'Invalid image file.'
                        ], 400);
                    }
                    
                    // Normalize the key to 'profile_picture' for compatibility with update_profile_form_details
                    $validated_files['profile_picture'] = $file;
                }
            }
        }

        $submitted_values = array_merge($sanitized_post, $validated_files);
        $status = self::update_profile_form_details(self::EDIT_FORM_KEY, $submitted_values, self::EDIT_PROFILE_FIELDS);

        if (isset($status['success']) && !$status['success']) {
            wp_send_json_error([
                'success' => false,
                'message' => $status['message']
            ], 400);
        }

        wp_send_json_success(['success' => true]);
    }

    public static function get_countries()
    {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/growtype-form';
        $file = $dir . '/countries.json';

        if (!file_exists($dir)) {
            wp_mkdir_p($dir); // recursively creates the folder
        }

        if (file_exists($file)) {
            $country_names = json_decode(file_get_contents($file), true);
        } else {
            // SECURITY: Use wp_remote_get instead of file_get_contents for external API calls
            $response = wp_remote_get('https://restcountries.com/v3.1/all?fields=name,languages,cca2', [
                'timeout' => 10,
                'sslverify' => true,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            if (is_wp_error($response)) {
                error_log('Growtype Form - Failed to fetch countries: ' . $response->get_error_message());
                return []; // Return empty array on failure
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log('Growtype Form - Countries API returned status: ' . $response_code);
                return [];
            }

            $body = wp_remote_retrieve_body($response);
            $countries = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Growtype Form - Invalid JSON from countries API: ' . json_last_error_msg());
                return [];
            }

            if (!is_array($countries)) {
                error_log('Growtype Form - Countries API did not return an array');
                return [];
            }

            $country_names = [];
            foreach ($countries as $country) {
                if (isset($country['cca2']) && isset($country['name']['common'])) {
                    $country_names[sanitize_text_field($country['cca2'])] = sanitize_text_field($country['name']['common']);
                }
            }

            // Cache the results
            if (!empty($country_names)) {
                file_put_contents($file, json_encode($country_names, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        $countries_collected = [];
        if (is_array($country_names)) {
            foreach ($country_names as $country_code => $country_name) {
                $countries_collected[] = [
                    'value' => sanitize_text_field($country_code),
                    'label' => sanitize_text_field($country_name)
                ];
            }
        }

        return $countries_collected;
    }

    public static function edit_profile_form_data($form_name)
    {
        $profile_fields = parent::edit_profile_form_fields($form_name, self::EDIT_PROFILE_FIELDS);
        $profile_fields = self::fill_profile_fields($profile_fields);

        return [
            "type" => "custom",
            "message" => "Profile information updated.",
            "main_fields" => $profile_fields,
            "submit_row" => [
                "cta" => [
                    [
                        "type" => "submit",
                        "action" => "delete",
                        "label" => "Delete account",
                        "class" => "btn btn-secondary mt-4 ms-auto"
                    ],
                    [
                        "type" => "submit",
                        "label" => "Save changes",
                        "class" => "btn btn-primary mt-4"
                    ]
                ]
            ]
        ];
    }

    public static function fill_profile_fields($profile_fields)
    {
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
                $profile_fields[$key]['options'] = self::get_countries();
                $profile_fields[$key]['value'] = $profile_field_name;
            } else {
                if (!isset($profile_field) || empty($profile_field['value'])) {
                    $profile_fields[$key]['value'] = $profile_field_name;
                }
            }
        }

        return $profile_fields;
    }
}
