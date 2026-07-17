<?php

/**
 * Growtype_Form_Email_Page
 *
 * Handles the virtual /gfemail/ page:
 *  - Renders an email capture form.
 *  - On submit: creates a gf_lead, then redirects to the gqresults page
 *    preserving the gqtoken query param.
 *
 * @package Growtype_Form
 * @since   1.0.0
 */
class Growtype_Form_Email_Page
{
    const PAGE_SLUG = "/gfemail/";
    const QUERY_VAR = "growtype_form_email_page";
    const AJAX_ACTION = "growtype_form_email_page_submit";

    // ── Boot ──────────────────────────────────────────────────────────────────

    /**
     * Register WordPress hooks.
     * Call once from the plugin loader.
     */
    public static function boot(): void
    {
        // Query var must be registered before WP parses the request.
        add_filter("query_vars", [static::class, "register_query_var"]);

        // Rewrite rule — must run on init.
        add_action("init", [static::class, "register_route"]);

        // Template handler — registered once, guards itself internally.
        add_action("template_redirect", [static::class, "render_page"]);

        // Body class — derived from slug so it tracks any filter override.
        add_filter("body_class", [static::class, "body_class"]);

        // AJAX submission — both logged-in and guest users.
        // AJAX handlers for the email-capture form.
        add_action("wp_ajax_" . self::AJAX_ACTION, [
            static::class,
            "handle_submit",
        ]);

        add_action("wp_ajax_nopriv_" . self::AJAX_ACTION, [
            static::class,
            "handle_submit",
        ]);
    }

    /**
     * Return the (possibly domain-overridden) page slug.
     * Override via: add_filter('growtype_form_email_page_slug', fn() => '/my-email/');
     */
    public static function get_slug(): string
    {
        return (string) apply_filters(
            "growtype_form_email_page_slug",
            self::PAGE_SLUG,
        );
    }

    /**
     * Return the full base URL for the email page.
     */
    public static function get_base_url(): string
    {
        return home_url(self::get_slug());
    }

    /**
     * Marketing params captured from URL and stored on leads.
     * Extend via: add_filter('growtype_form_email_marketing_keys', fn($keys) => array_merge($keys, ['my_param']));
     */
    public static function marketing_keys(): array
    {
        return (array) apply_filters("growtype_form_email_marketing_keys", [
            "utm_source",
            "utm_medium",
            "utm_campaign",
            "utm_term",
            "utm_content",
            "fbclid",
            "gclid",
        ]);
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    /**
     * Expose the query var to WordPress.
     */
    public static function register_query_var(array $vars): array
    {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    /**
     * Register the /gfemail/ rewrite rule so WordPress handles the URL
     * without a real page in the database.
     */
    public static function register_route(): void
    {
        $slug = trim(self::get_slug(), "/"); // e.g. 'gfemail'

        add_rewrite_rule(
            "^" . preg_quote($slug, "/") . '/?$',
            "index.php?" . self::QUERY_VAR . "=1",
            "top",
        );
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Add a body class when the email page is active.
     */
    public static function body_class(array $classes): array
    {
        if (!get_query_var(self::QUERY_VAR)) {
            return $classes;
        }

        $slug = trim(self::get_slug(), "/");
        $classes[] = "page-" . sanitize_html_class($slug);

        return $classes;
    }

    /**
     * Render the email page.
     * Fires on template_redirect — guards itself with the query var check.
     */
    /**
     * Render the email page.
     * Fires on template_redirect — guards itself with the query var check.
     */
    public static function render_page(): void
    {
        if (!get_query_var(self::QUERY_VAR)) {
            return;
        }

        header("X-Robots-Tag: noindex, nofollow", true);
        nocache_headers();
        status_header(200);

        // Preserve gqtoken so the form can forward it on redirect.
        $gqtoken = sanitize_text_field($_GET["gqtoken"] ?? "");
        $redirect_url = apply_filters(
            "growtype_form_email_page_redirect_url",
            add_query_arg("gqtoken", $gqtoken, home_url("/gqresults/")),
            $gqtoken,
        );

        $ajax_url = admin_url("admin-ajax.php");
        $ajax_action = self::AJAX_ACTION;
        $nonce = wp_create_nonce(self::AJAX_ACTION);

        // Reuse the existing newsletter component view
        $submit_label_text = __("See Results", "growtype-form");
        $form_html = growtype_form_include_view("components.forms.newsletter", [
            "submit_label" => $submit_label_text,
            "email_placeholder" => __("Your email", "growtype-form"),
            "email_label" => "",
            "terms" => false,
        ]);

        $info_label =
            '<div class="gfemail-privacy-info" style="line-height:150%;">' .
            '<span style="color:rgb(30,31,33);">' .
            esc_html__(
                "By continuing, you indicate that you've read and agree to our ",
            ) .
            "</span>" .
            '<a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" data-privacy-type="terms" style="color:var(--ps-color-primary,#4db8a0);text-decoration:underline;">' .
            esc_html__("terms & conditions", "growtype-form") .
            "</a>" .
            '<span style="color:rgb(30,31,33);"> ' .
            esc_html__("and", "growtype-form") .
            " </span>" .
            '<a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal" data-privacy-type="privacy" style="color:var(--ps-color-primary,#4db8a0);text-decoration:underline;">' .
            esc_html__("privacy policy", "growtype-form") .
            "</a>" .
            "</div>";

        $email_explanation =
            '<div class="gfemail-privacy-notice flex w-full flex-nowrap items-start gap-2">' .
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.01198 22H16.988C18.3398 22 19 21.3439 19 19.9088V12.3844C19 11.0928 18.4551 10.4264 17.3338 10.3137V7.73039C17.3338 3.86571 14.7455 2 12 2C9.25449 2 6.66617 3.86571 6.66617 7.73039V10.3649C5.63922 10.5187 5 11.1748 5 12.3844V19.9088C5 21.3439 5.66018 22 7.01198 22ZM8.35329 7.51512C8.35329 4.94208 10.0404 3.57868 12 3.57868C13.9596 3.57868 15.6467 4.94208 15.6467 7.51512V10.3034L8.35329 10.3137V7.51512Z" fill="#BBBBBB"></path></svg>' .
            '<span class="max-w-[calc(100%-60px)] text-xs text-token-neutral-800"><p style="text-align: start;"><span style="color: rgb(187,187,187);">' .
            esc_html__(
                "We respect your privacy and are committed to protecting your personal data. We'll email you a copy of your results for convenient access. You can unsubscribe anytime.",
                "growtype-form",
            ) .
            '</span></p></span>' .
            '</div>';

        $show_social_proof = apply_filters('growtype_form_email_page_show_social_proof', true);

        $social_proof = '';
        if ($show_social_proof && class_exists('Growtype_Wc_Happy_Customers')) {
            $social_proof = Growtype_Wc_Happy_Customers::render([
                'gender' => 'mix',
                'amount' => 5,
                'label' => '<b>100k+</b> users have joined us!',
                'shuffle' => 'false',
            ]);
        }

        $show_info_label = apply_filters('growtype_form_email_page_show_info_label', false);

        $pre_submit = $social_proof . $email_explanation;
        if ($show_info_label) {
            $pre_submit = $info_label . $social_proof . $email_explanation;
        }

        $form_html = str_replace(
            '<button type="submit" class="btn btn-primary">' .
                $submit_label_text,
            $pre_submit .
                '<button type="submit" class="btn btn-primary">' .
                $submit_label_text,
            $form_html,
        );

        // Generate premium glassmorphism form wrapper around the reused component
        ob_start();
        ?>
        <div class="container">
<div class="card gfemail-container">
            <h2><?php _e("Your results are ready", "growtype-form"); ?></h2>
            <p><?php _e(
                "Enter your email to get your personalized Communication plan",
                "growtype-form",
            ); ?></p>

            <?php echo $form_html; ?>
        </div>
        </div>

        <style>
        .gfemail-container {
            width: 100%;
            max-width: 500px;
            margin: 60px auto;
            text-align: center;
        }
        .gfemail-container h2 {
            font-size: 28px;
            font-weight: 700;
        }
        .gfemail-container p {
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .gfemail-privacy-info {
            font-size: 12px;
            text-align: left;
            padding-top:10px;
        }
        .gfemail-privacy-info a{
            font-size: 12px;
        }
        .gfemail-container .status-message {
            font-size: 14px;
            text-align: left;
        }
        .growtype-form-newsletter {
            padding-top: 20px;
        }
        /* Hide the extra submit button inside input wrapper */
        .gfemail-container .input-wrapper-inner button[type="submit"] {
            display: none !important;
        }

        .gfemail-container input[type="email"] {
            width: 100%;
        }

        /* Email input icon */
        .gfemail-container .input-wrapper-inner {
            position: relative;
        }
        .gfemail-container .input-wrapper-inner input[type="email"] {
            padding-left: 40px!important;
        }
        .gfemail-container .input-wrapper-inner .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: rgb(187, 187, 187);
            pointer-events: none;
            z-index: 1;
        }

        .gfemail-container button[type="submit"] {
            width: 100%;
            margin-top: 20px;
        }

        .gfemail-container label {
            display: block;
            font-size: 13px;
            text-align: left;
        }

        /* Mobile responsive adjustments */
        @media (max-width: 576px) {
            .gfemail-container {
                padding: 28px 15px;
                margin: 0 auto;
                border-radius: 16px;
            }
            .gfemail-container h2 {
                font-size: 24px;
            }
            .gfemail-container p {
                font-size: 14px;
            }
            .gfemail-privacy-info {
                font-size: 12px;
            }
        }

        /* Override happy customers banner inside email page */
        .gfemail-container .gt-happy-customers {
            background: none;
                margin-top: 10px;
                margin-left: auto;
                margin-right: auto;
        }

        /* Privacy notice */
        .gfemail-privacy-notice {
            margin-top: 15px;
            margin-bottom: 15px;
            padding: 0;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            width: 100%;
            text-align: left;
            color: rgb(187, 187, 187);
            font-size: 12px;
            line-height: 1.5;
        }
        .gfemail-privacy-notice svg {
            flex-shrink: 0;
            margin-top: 2px;
            width: 24px;
            height: 24px;
            color: rgb(187, 187, 187);
        }
        .gfemail-privacy-notice > span {
            flex: 1;
            min-width: 0;
        }
        .gfemail-privacy-notice p {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
            color: rgb(187, 187, 187);
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Remove the default newsletter handler — it sends without our nonce
            $('.growtype-form-newsletter').off('submit');

            // Target the reused newsletter component form and override its standard submit action
            $('.growtype-form-newsletter').on('submit', function(e) {
                e.stopImmediatePropagation();
                e.preventDefault();

                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');
                var $status = $form.find('.status-message');

                $submitBtn.prop('disabled', true);
                $status.hide().removeClass('alert alert-danger alert-success').text('');

                var formData = $form.serializeArray();
                formData.push({name: "action", value: "<?php echo esc_js(
                    $ajax_action,
                ); ?>"});
                formData.push({name: "nonce", value: "<?php echo esc_js(
                    $nonce,
                ); ?>"});
                formData.push({name: "gqtoken", value: "<?php echo esc_js(
                    $gqtoken,
                ); ?>"});
                <?php
                $marketing_keys = self::marketing_keys();
                foreach ($marketing_keys as $key) {
                    $val = sanitize_text_field($_GET[$key] ?? "");
                    if ($val !== "") {
                        echo 'formData.push({name: "' .
                            esc_js($key) .
                            '", value: "' .
                            esc_js($val) .
                            '"});';
                    }
                }
                ?>

                $.ajax({
                    url: '<?php echo esc_url($ajax_url); ?>',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success && response.data && response.data.redirect_url) {
                            if (typeof window.growtypeAnalyticsCapture === 'function') {
                                window.growtypeAnalyticsCapture('growtype_analytics_complete_registration_submit', {
                                    event: 'complete_registration',
                                    source: 'gfemail'
                                });
                            } else {
                                window.dataLayer = window.dataLayer || [];
                                window.dataLayer.push({
                                    event: 'growtype_analytics_complete_registration_submit',
                                    source: 'gfemail'
                                });
                            }

                            $status.addClass('alert alert-success').text('Success! Redirecting to results...').show();
                            window.location.href = response.data.redirect_url;
                        } else {
                            var msg = (response.data && response.data.message) ? response.data.message : 'An error occurred. Please try again.';
                            $status.addClass('alert alert-danger').text(msg).show();
                            $submitBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        var message = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            message = xhr.responseJSON.data.message;
                        }
                        $status.addClass('alert alert-danger').text(message).show();
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
        $content = ob_get_clean();

        echo growtype_form_include_view("pages.email.index", [
            "content" => $content,
        ]);
        exit();
    }

    // ── Submission ────────────────────────────────────────────────────────────

    /**
     * Handle the AJAX form submission.
     * - Validates email.
     * - Creates / updates a gf_lead.
     * - Returns redirect URL on success.
     */
    public static function handle_submit(): void
    {
        // CSRF check.
        if (
            !isset($_POST["nonce"]) ||
            !wp_verify_nonce($_POST["nonce"], self::AJAX_ACTION)
        ) {
            wp_send_json_error(
                ["message" => __("Security check failed.", "growtype-form")],
                403,
            );
        }

        $email = sanitize_email(
            $_POST["email"] ?? ($_POST["newsletter_email"] ?? ""),
        );

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(
                [
                    "message" => __(
                        "Please enter a valid email address.",
                        "growtype-form",
                    ),
                ],
                400,
            );
        }

        // Disposable email check — same as signup
        if (
            class_exists("Growtype_Form_Crud_Validation") &&
            method_exists(
                "Growtype_Form_Crud_Validation",
                "disposable_email_validation",
            )
        ) {
            $disposable_result = Growtype_Form_Crud_Validation::disposable_email_validation(
                $email,
            );
            if (!empty($disposable_result["message"])) {
                wp_send_json_error(
                    ["message" => $disposable_result["message"]],
                    400,
                );
            }
        }

        // Create lead using existing class and method.
        if (!class_exists("Growtype_Form_Admin_Lead")) {
            if (defined("GROWTYPE_FORM_PATH")) {
                require_once GROWTYPE_FORM_PATH .
                    "admin/pages/leads/class-growtype-form-admin-lead.php";
            }
        }

        $gqtoken = sanitize_text_field($_POST["gqtoken"] ?? "");

        if (class_exists("Growtype_Form_Admin_Lead")) {
            $extra_details = [
                "gqtoken" => $gqtoken,
                "source" => "gfemail",
                "ip" => $_SERVER["REMOTE_ADDR"] ?? "",
                "user_agent" => $_SERVER["HTTP_USER_AGENT"] ?? "",
            ];

            $marketing_keys = self::marketing_keys();
            foreach ($marketing_keys as $key) {
                $val = sanitize_text_field($_POST[$key] ?? "");
                if ($val !== "") {
                    $extra_details[$key] = $val;
                }
            }

            Growtype_Form_Admin_Lead::insert([
                "title" => $email,
                "extra_details" => $extra_details,
            ]);

            // Also store the email in the quiz result's extra_details
            if (
                !empty($gqtoken) &&
                class_exists("Growtype_Quiz_Result_Crud") &&
                method_exists(
                    "Growtype_Quiz_Result_Crud",
                    "get_quiz_single_result_data_by_unique_hash",
                )
            ) {
                $quiz_result = Growtype_Quiz_Result_Crud::get_quiz_single_result_data_by_unique_hash(
                    $gqtoken,
                );
                if (!empty($quiz_result)) {
                    $existing = !empty($quiz_result["extra_details"])
                        ? json_decode($quiz_result["extra_details"], true)
                        : [];
                    $existing["email"] = $email;
                    Growtype_Quiz_Result_Crud::update_quiz_single_result(
                        $quiz_result["id"],
                        [
                            "extra_details" => json_encode($existing),
                        ],
                    );
                }
            }

            do_action("growtype_form_email_page_submitted", $email, [
                "gqtoken" => $gqtoken,
                "event_source_url" => !empty($_SERVER["REQUEST_URI"])
                    ? home_url(wp_unslash($_SERVER["REQUEST_URI"]))
                    : home_url(self::get_slug()),
            ]);
        }

        // Build the redirect URL (gqtoken was sent from the form).
        $redirect_url = apply_filters(
            "growtype_form_email_page_redirect_url",
            add_query_arg("gqtoken", $gqtoken, home_url("/gqresults/")),
            $gqtoken,
        );

        wp_send_json_success(["redirect_url" => $redirect_url]);
    }
}

Growtype_Form_Email_Page::boot();
