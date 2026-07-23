<?php

/**
 * Class Growtype_Form_Feedback
 */
class Growtype_Form_Feedback
{
    const FORM_NAME = 'feedback';

    public function __construct()
    {
        add_filter('growtype_form_get_growtype_form_data', array ($this, 'growtype_form_get_growtype_form_data_callback'), 10, 2);
        add_filter('growtype_form_submitted_values_redirect_url', array ($this, 'growtype_form_redirect_url_callback'), 10, 4);
    }

    /**
     * @param $redirect_url
     * @param $form_data
     * @param $submitted_data
     * @param $submit_data
     * @return string
     */
    public function growtype_form_redirect_url_callback($redirect_url, $form_data, $submitted_data, $submit_data)
    {
        if (($form_data['form_name'] ?? '') === self::FORM_NAME) {
            $redirect_url = home_url('/feedback/');
        }

        return $redirect_url;
    }

    /**
     * @param $form_data
     * @param $form_name
     * @return array|mixed
     */
    public function growtype_form_get_growtype_form_data_callback($form_data, $form_name)
    {
        if ($form_name === self::FORM_NAME && empty($form_data)) {
            $form_data = [
                "form_name" => self::FORM_NAME,
                "success_message" => __("Thank you for your feedback. We appreciate your valuable contribution.", "growtype-form"),
                "main_fields" => [
                    [
                        "type" => "select",
                        "name" => "satisfaction",
                        "label" => __("How satisfied are you with the overall experience of our application?", "growtype-form"),
                        "placeholder" => __("Please select", "growtype-form"),
                        "class" => "col-12",
                        "required" => "true",
                        "options" => [
                            ["value" => "1", "label" => __("1 - Not Satisfied", "growtype-form")],
                            ["value" => "2", "label" => __("2 - Somewhat Satisfied", "growtype-form")],
                            ["value" => "3", "label" => __("3 - Neutral", "growtype-form")],
                            ["value" => "4", "label" => __("4 - Satisfied", "growtype-form")],
                            ["value" => "5", "label" => __("5 - Very Satisfied", "growtype-form")]
                        ]
                    ],
                    [
                        "type" => "textarea",
                        "name" => "features_helpful",
                        "label" => __("What features do you find most helpful or enjoyable in the application?", "growtype-form"),
                        "placeholder" => __("e.g. fast response times, realistic roleplay, variety of characters...", "growtype-form"),
                        "class" => "col-12",
                        "required" => "true"
                    ],
                    [
                        "type" => "textarea",
                        "name" => "features_improvement",
                        "label" => __("Which features, if any, do you think need improvement for a better user experience?", "growtype-form"),
                        "placeholder" => __("e.g. better memory for characters, more customization options...", "growtype-form"),
                        "class" => "col-12",
                        "required" => "true"
                    ],
                    [
                        "type" => "textarea",
                        "name" => "issues_bugs",
                        "label" => __("Have you encountered any issues or bugs that affected your overall user experience? If so, please describe.", "growtype-form"),
                        "placeholder" => __("Describe any technical glitches or unexpected behavior you encountered...", "growtype-form"),
                        "class" => "col-12",
                        "required" => "false"
                    ],
                    [
                        "type" => "textarea",
                        "name" => "improvements_suggestions",
                        "label" => __("What improvements would you suggest to enhance the overall user-friendliness and satisfaction with the application?", "growtype-form"),
                        "placeholder" => __("Share your ideas on how we can make the platform better for you...", "growtype-form"),
                        "class" => "col-12",
                        "required" => "true"
                    ],
                    [
                        "type" => "textarea",
                        "name" => "additional_comments",
                        "label" => __("Are there any other comments or suggestions you would like to share regarding your experience with our application?", "growtype-form"),
                        "placeholder" => __("Anything else you'd like to tell us?", "growtype-form"),
                        "class" => "col-12",
                        "required" => "false"
                    ]
                ],
                "submit_row" => [
                    "cta" => [
                        [
                            "type" => "submit",
                            "label" => __("Send feedback", "growtype-form"),
                            "class" => "btn btn-primary col-12"
                        ]
                    ]
                ]
            ];
        }

        return $form_data;
    }
}
