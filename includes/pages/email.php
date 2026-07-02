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
        $submit_label_text = __("See My Results", "growtype-form");
        $form_html = growtype_form_include_view("components.forms.newsletter", [
            "submit_label" => $submit_label_text,
            "email_placeholder" => __("Your email", "growtype-form"),
            "email_label" => __(
                "Where should we send your results?",
                "growtype-form",
            ),
            "terms" => false,
        ]);

        // Add info label after the input (right before the submit button)
        $info_label =
            '<div class="gfemail-privacy-info">' .
            esc_html__(
                "🔒 Your results are private. No spam, ever.",
                "growtype-form",
            ) .
            "</div>";
        $form_html = str_replace(
            '<button type="submit" class="btn btn-primary">' .
                $submit_label_text,
            $info_label .
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
                "Please enter your email to view your personalized results.",
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
            font-size: 15px;
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .gfemail-privacy-info {
            font-size: 13px;
            margin-top: 15px;
            margin-bottom: 5px;
            display: flex;
            gap: 6px;
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
                padding: 28px 20px;
                margin: 30px auto;
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
