<?php

/**
 * Class Growtype_Form_General_Submission
 *
 * Handles AJAX form submissions for [growtype_form].
 * Registered via Growtype_Form_General constructor.
 */
class Growtype_Form_General_Submission
{
    const AJAX_ACTION = 'growtype_form_submission';

    public function __construct()
    {
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'handle']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'handle']);
    }

    /**
     * AJAX callback — parses serialised form data, validates, saves as lead.
     */
    public function handle(): void
    {
        // ── Parse & sanitise ────────────────────────────────────────────
        parse_str(wp_unslash($_POST['postdata']['form'] ?? ''), $raw);

        $form_name = sanitize_key($raw['growtype_form_name'] ?? '');
        $allowed = $this->field_keys($form_name);

        $data = [];
        foreach ($raw as $key => $value) {
            if ($this->is_internal_key($key)) {
                continue;
            }
            if (!empty($allowed) && !in_array($key, $allowed, true)) {
                continue;
            }
            $data[$key] = is_email($value)
                ? sanitize_email($value)
                : sanitize_text_field($value);
        }

        // ── Require an email ────────────────────────────────────────────
        $email = $data['email'] ?? $this->find_email($data);
        if (empty($email) || !is_email($email)) {
            wp_send_json([
                'success' => false,
                'message' => __('Please provide a valid email address.', 'growtype-form'),
            ], 400);
        }
        $data['email'] = $email;

        // ── Filter before save ──────────────────────────────────────────
        $data = apply_filters('growtype_form_submission_data', $data, $form_name, $raw);

        // ── Save as lead ────────────────────────────────────────────────
        $response = apply_filters('growtype_form_newsletter_submission_save_data', $data);

        if (empty($response) || is_wp_error($response) || !($response['success'] ?? true)) {
            $message = apply_filters(
                'growtype_form_submission_error_message',
                $response['message'] ?? __('Something went wrong. Please try again.', 'growtype-form'),
                $response,
                $form_name
            );
            wp_send_json(['success' => false, 'message' => $message], 400);
        }

        $message = apply_filters(
            'growtype_form_submission_success_message',
            $response['message'] ?? __('Your submission was successful. Thank you!', 'growtype-form'),
            $response,
            $form_name
        );

        do_action('growtype_form_submission_success', $data, $form_name, $response);

        wp_send_json(['success' => true, 'message' => $message]);
    }

    /**
     * Get defined field names for a form.
     */
    private function field_keys(string $form_name): array
    {
        $form_data = Growtype_Form_Crud::get_growtype_form_data($form_name);
        $keys = [];

        foreach (($form_data['main_fields'] ?? []) as $field) {
            $name = $field['name'] ?? $field['key'] ?? null;
            if ($name) {
                $keys[] = $name;
            }
        }

        return $keys;
    }

    /**
     * System / honeypot keys to skip.
     */
    private function is_internal_key(string $key): bool
    {
        return in_array($key, [
            'growtype_form_nonce',
            '_wp_http_referer',
            'email_address_s_c',
            'important_data_s_c_h',
            'growtype_form_submit_action',
            'growtype_form_name',
            'form_language',
        ], true);
    }

    /**
     * Find the first valid email in the data array.
     */
    private function find_email(array $data): string
    {
        foreach ($data as $value) {
            if (is_email($value)) {
                return $value;
            }
        }
        return '';
    }
}
