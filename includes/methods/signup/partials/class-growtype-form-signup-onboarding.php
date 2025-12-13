<?php

/**
 *
 */
class Growtype_Form_Signup_Onboarding
{
    const URL_PATH = 'onboarding';
    const EDIT_FORM_KEY = 'growtype_form_onboarding_step';

    public function __construct()
    {
        add_action('init', array ($this, 'custom_url'), 1);

        add_filter('query_vars', array ($this, 'query_vars'));

        add_action('template_redirect', array ($this, 'custom_url_template'));

        add_filter('growtype_form_redirect_url_after_signup', array ($this, 'redirect_after_signup'));

        add_filter('growtype_form_upload_post_custom', function ($form_data, $submitted_values, $form_name) {
            if ($form_name === self::EDIT_FORM_KEY) {
                $onboarding_step = $submitted_values['data']['onboarding_step'];

                $this->save_onboarding_step($onboarding_step, $submitted_values, $form_name);

                $steps = self::get_steps();

                $next_key = '';
                $current_step_found = false;
                foreach ($steps as $step_key => $step) {
                    if ($current_step_found) {
                        $next_key = $step_key;
                        break;
                    }

                    if ($step_key === $onboarding_step) {
                        $current_step_found = true;
                    }
                }

                $form_data['success'] = true;
                $form_data['message'] = '';

                if (empty($next_key)) {
                    $form_data['redirect_url'] = self::redirect_url_after_onboarding();
                } else {
                    $form_data['redirect_url'] = home_url('/onboarding/' . $next_key . '/');
                }
            }

            return $form_data;
        }, 0, 3);
    }

    public static function redirect_url_after_onboarding()
    {
        return apply_filters('growtype_form_redirect_url_after_onboarding', home_url());
    }

    /**
     * @return void
     */
    function custom_url()
    {
        if ($this->is_enabled()) {
            add_rewrite_rule(
                '^' . self::URL_PATH . '/([^/]*)/?$',
                'index.php?' . self::URL_PATH . '_page=$matches[1]',
                'top'
            );

            add_rewrite_rule(
                '^' . self::URL_PATH . '/?$',
                'index.php?' . self::URL_PATH . '_page=1',
                'top'
            );
        }
    }

    function query_vars($vars)
    {
        $vars[] = self::URL_PATH . '_page';
        return $vars;
    }

    /**
     * @return void
     */
    function custom_url_template()
    {
        if (growtype_form_current_page_is_onboarding_page()) {
            if ($this->is_enabled()) {

                if (!is_user_logged_in()) {
                    wp_redirect(wp_login_url());
                    exit;
                }

                $step = get_query_var(self::URL_PATH . '_page');

                $steps = self::get_steps();

                if ($step === '1') {
                    if (!empty($steps)) {
                        $first_step = array_key_first($steps);
                        wp_redirect(home_url(self::URL_PATH . '/' . $first_step));
                        exit;
                    }
                }

                add_filter('body_class', function ($classes) use ($step) {
                    $classes[] = 'page-onboarding';
                    $classes[] = 'page-onboarding-step-' . $step;

                    return $classes;
                });

                $current_step = $step ?? 'welcome';
                $all_steps = $steps ?? [];
                $step_keys = array_keys($all_steps);
                $current_step_index = array_search($current_step, $step_keys);
                $current_step_data = $all_steps[$current_step] ?? [];
                $total_steps = count($all_steps);
                $is_last_step = ($current_step_index === $total_steps - 1);
                $is_first_step = ($current_step_index === 0);

                $form_fields = $current_step_data['form_fields'] ?? [];

                $form_fields['main_fields'][] = [
                    'type' => 'hidden',
                    'name' => 'onboarding_step',
                    'value' => $step,
                ];

                $next_step = null;
                if (!$is_last_step && isset($step_keys[$current_step_index + 1])) {
                    $next_step = $step_keys[$current_step_index + 1];
                }

                $prev_step = null;
                if (!$is_first_step && isset($step_keys[$current_step_index - 1])) {
                    $prev_step = $step_keys[$current_step_index - 1];
                }

                echo growtype_form_include_view('signup.onboarding', [
                    'step' => $step,
                    'steps' => self::get_steps(),
                    'step_keys' => $step_keys,
                    'current_step_index' => $current_step_index,
                    'current_step_data' => $current_step_data,
                    'form_fields' => $form_fields,
                    'total_steps' => $total_steps,
                    'next_step' => $next_step,
                    'prev_step' => $prev_step,
                    'is_last_step' => $is_last_step,
                    'is_first_step' => $is_first_step,
                ]);

                exit;
            }
        }
    }

    function redirect_after_signup($url)
    {
        if ($this->is_enabled()) {
            return home_url(self::URL_PATH);
        }

        return $url;
    }

    function is_enabled()
    {
        return get_option('growtype_form_settings_signups_onboarding_enabled');
    }

    public static function get_steps()
    {
        $steps = [
            'welcome' => [
                'title' => 'Let’s get to know you',
                'form_fields' => [
                    'type' => 'custom',
                    'main_fields' => [
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
                    ],
//                    'confirmation_fields' => [
//                        [
//                            "type" => "checkbox",
//                            "name" => "terms_and_conditions",
//                            "label" => "I agree to <a href='#' data-bs-toggle='modal' data-bs-target='#termsModal'>Terms & Conditions</a> and <a href='#' data-bs-toggle='modal' data-bs-target='#privacyModal'>Privacy Policy</a>.",
//                            "required" => true
//                        ]
//                    ],
                    "submit_row" => [
                        "cta" => [
                            [
                                "type" => "submit",
                                "label" => "Next",
                                "class" => "btn btn-primary mt-4"
                            ]
                        ]
                    ]
                ]
            ],
            'finish' => [
                'title' => 'Add your photo',
                'form_fields' => [
                    'type' => 'custom',
                    'main_fields' => [
                        [
                            "type" => "fully_custom",
                            "name" => "profile_picture",
                        ]
                    ],
                    "submit_row" => [
                        "cta" => [
                            [
                                "type" => "submit",
                                "label" => "Next",
                                "class" => "btn btn-primary mt-4"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $steps = apply_filters('growtype_form_onboarding_steps_before_fill', $steps);

        foreach ($steps as $step_key => $step) {
            if (isset($steps[$step_key]['form_fields']['main_fields'])) {
                $steps[$step_key]['form_fields']['main_fields'] = Growtype_Form_Profile_Edit::fill_profile_fields($steps[$step_key]['form_fields']['main_fields']);
            }
        }

        return apply_filters('growtype_form_onboarding_steps_after_fill', $steps);
    }

    public function save_onboarding_step($onboarding_step, $submitted_values, $form_name)
    {
        $steps = self::get_steps();

        $main_fields = $steps[$onboarding_step]['form_fields']['main_fields'] ?? [];

        $result = Growtype_Form_Profile_Edit::update_profile_form_details($form_name, $submitted_values['data'], $main_fields);

        $user_id = get_current_user_id();

        switch ($onboarding_step) {
            case 'finish':
                update_user_meta($user_id, 'onboarding_completed', true);
                update_user_meta($user_id, 'onboarding_completed_at', current_time('mysql'));
                break;
        }

        update_user_meta($user_id, 'onboarding_last_step', $onboarding_step);
    }
}
