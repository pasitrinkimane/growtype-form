<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Newsletter
{
    public function __construct()
    {
        if (!is_admin()) {
            /**
             * Newsletter shortcode
             */
            add_shortcode('growtype_form_newsletter', array ($this, 'growtype_form_newsletter_shortcode'));
        }

        /**
         * Newsletter
         */
        add_action('wp_ajax_growtype_form_newsletter_submission', array ($this, 'growtype_form_newsletter_submission_callback'));
        add_action('wp_ajax_nopriv_growtype_form_newsletter_submission', array ($this, 'growtype_form_newsletter_submission_callback'));
    }

    /**
     * Newsletter shortcode
     */
    function growtype_form_newsletter_shortcode($attr = [])
    {
        $attr = !empty($attr) ? $attr : [];

        return growtype_form_include_view('components.forms.newsletter', $attr);
    }

    /**
     * Newsletter submission callback
     */
    function growtype_form_newsletter_submission_callback()
    {
        $newsletter_email = isset($_REQUEST['newsletter_email']) ? $_REQUEST['newsletter_email'] : '';

        $data = [
            'email' => $newsletter_email
        ];

        $response = apply_filters('growtype_form_newsletter_submission_save_data', $data);

        if (class_exists('Flamingo_Contact')) {
            $response = Flamingo_Contact::add($data);
        }

        if (!empty($response) && !$response) {
            return wp_send_json(
                [
                    'message' => __('Something went wrong. Please contact us for help.', 'growtype')
                ], 400);
        }

        return wp_send_json(
            [
                'message' => __('Your subscription is successful. Thank you.', 'growtype')
            ], 200);
    }
}
