<?php

/**
 * Class Growtype_Form_Upload_Data
 */
class Growtype_Form_Newsletter
{
    const FORM_NAME = 'newsletter';

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
        add_action('growtype_form_newsletter_submission_save_data', array ($this, 'growtype_form_newsletter_submission_save_data_callback'));
        add_action('wp_loaded', array ($this, 'growtype_form_newsletter_unsubscribe'));
    }

    /**
     * Newsletter shortcode
     */
    function growtype_form_newsletter_shortcode($attr = [])
    {
        wp_enqueue_style('growtype-form-newsletter', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/newsletter/index.css', array (), GROWTYPE_FORM_VERSION, 'all');

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

        if (!empty($response) && !$response || is_wp_error($response) || (isset($response['success']) && !$response['success'])) {
            return wp_send_json(
                [
                    'messages' => isset($response['messages']) ? $response['messages'] : __('Something went wrong. Please contact us for help.', 'growtype-form')
                ], 400);
        }

        return wp_send_json(
            [
                'messages' => __('Your subscription is successful. Thank you.', 'growtype')
            ], 200);
    }

    function growtype_form_newsletter_submission_save_data_callback($data)
    {
        $email = isset($data['email']) ? $data['email'] : null;

        if (empty($email)) {
            return $data;
        }

        $submission = growtype_form_save_submission(
            [
                'form_name' => self::FORM_NAME,
                'post_title_name_key' => 'email',
            ],
            [
                'data' => [
                    'email' => $email
                ]
            ]
        );

        $submission['messages'] = __('Your subscription is successful. Thank you.', 'growtype-form');

        if ($submission['success'] === false) {
            $submission['messages'] = __('You are already subscribed.', 'growtype-form');
        }

        return $submission;
    }

    public static function unsubscribe_url($email)
    {
        $encoded_email = str_replace('+', '%2B', $email);
        $encoded_email = urlencode($encoded_email);

        return home_url() . '/growtype-form/newsletter/unsubscribe/user/?email=' . $encoded_email;
    }

    function growtype_form_newsletter_unsubscribe()
    {
        /**
         * Unsubscribe
         */
        if (strpos($_SERVER['REQUEST_URI'], '/growtype-form/newsletter/unsubscribe/user/') !== false) {
            $email = isset($_GET['email']) ? urldecode($_GET['email']) : null;

            if (empty($email)) {
                return;
            }

            $leads = get_posts([
                'title' => $email,
                'post_type' => Growtype_Form_Admin_Lead::POST_TYPE_NAME,
            ]);

            foreach ($leads as $lead) {
                if ($lead->post_title === $email) {
                    $newsletter_unsubscribed = get_post_meta($lead->ID, 'newsletter_unsubscribed', true);

                    if (empty($newsletter_unsubscribed) || $newsletter_unsubscribed === false) {
                        update_post_meta($lead->ID, 'newsletter_unsubscribed', true);
                    }
                }
            }

            wp_redirect(home_url() . '/growtype-form/newsletter/unsubscribed/');
            die();
        }

        /**
         * Final view
         */
        if (strpos($_SERVER['REQUEST_URI'], '/growtype-form/newsletter/unsubscribed/') !== false) {
            wp_enqueue_style('growtype-form-newsletter', GROWTYPE_FORM_URL_PUBLIC . 'styles/forms/newsletter/index.css', array (), GROWTYPE_FORM_VERSION, 'all');

            add_filter('body_class', function ($classes) {
                $classes[] = 'page-newsletter-unsubscribe';
                return $classes;
            });

            echo growtype_form_include_view('newsletter.unsubscribe');
            die();
        }
    }
}
