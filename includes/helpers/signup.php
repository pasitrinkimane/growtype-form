<?php

/**
 * @return array|WP_Post|null
 */
function growtype_form_signup_page_id()
{
    return get_option("growtype_form_signup_page", "default");
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_signup_page_is_active()
{
    $page_ID = growtype_form_signup_page_id();

    if (isset($_SERVER["REQUEST_URI"]) && $page_ID === "default") {
        $current_url_slug = str_replace("/", "", $_SERVER["REQUEST_URI"]);

        return strtok($current_url_slug, "?") ===
            Growtype_Form_Signup::URL_PATH;
    }

    $post = get_post($page_ID);

    return !empty($post) &&
        !empty($post->post_name) &&
        strpos($_SERVER["REQUEST_URI"], $post->post_name) !== false;
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_signup_page_url($query_vars = [])
{
    $url = !empty(growtype_form_signup_page_id())
        ? get_permalink(growtype_form_signup_page_id())
        : null;

    if (
        !empty(growtype_form_signup_page_id()) &&
        growtype_form_signup_page_id() === "default"
    ) {
        $url = home_url(Growtype_Form_Signup::URL_PATH);
    }

    if (!empty($query_vars)) {
        $url .= "?" . http_build_query($query_vars);
    }

    return $url;
}

/**
 * Internal helper: resolve a legal page's content from its option.
 *
 * @param string $option_name   WP option key (e.g. "growtype_form_signup_privacy_page").
 * @param string $template_path Blade template path (e.g. "views/page-privacy-policy.blade.php").
 * @param string $fallback_slug Page slug fallback (e.g. "privacy-policy").
 * @return string
 */
function _growtype_form_get_signup_legal_page_content(
    $option_name,
    $template_path,
    $fallback_slug,
) {
    $option = get_option($option_name);

    if (empty($option) || $option === "none") {
        return "";
    }

    if ($option === "default_template") {
        $blade_path = get_stylesheet_directory() . "/" . $template_path;

        if (file_exists($blade_path)) {
            $raw = file_get_contents($blade_path);

            if (
                preg_match(
                    "/@section\\('content'\\)(.*?)@endsection/s",
                    $raw,
                    $matches,
                )
            ) {
                $content = $matches[1];

                // Convert @php blocks to PHP tags
                $content = preg_replace(
                    "/@php\\s*(.*?)\\s*@endphp/s",
                    '<?php $1 ?>',
                    $content,
                );

                // Convert {{ }} to PHP echo
                $content = preg_replace(
                    "/\\{\\{\\s*(.+?)\\s*\\}\\}/s",
                    '<?php echo $1; ?>',
                    $content,
                );

                ob_start();
                eval("?>" . $content . "<?php ");
                $content = ob_get_clean();

                if (!empty(trim($content))) {
                    return $content;
                }
            }
        }

        $found = get_pages([
            "meta_key" => "_wp_page_template",
            "meta_value" => $template_path,
            "hierarchical" => 0,
            "number" => 1,
        ]);

        if (empty($found)) {
            $match = get_page_by_path($fallback_slug);
            $found = !empty($match) ? [$match] : [];
        }

        if (!empty($found)) {
            $post = $found[0];
            if (!empty($post)) {
                return apply_filters("the_content", $post->post_content);
            }
        }
    } else {
        $post = get_post($option);
        if (!empty($post)) {
            return apply_filters("the_content", $post->post_content);
        }
    }

    return "";
}

/**
 * Get privacy page content for use in modals, etc.
 *
 * @return string
 */
function growtype_form_get_signup_privacy_page_content()
{
    return _growtype_form_get_signup_legal_page_content(
        "growtype_form_signup_privacy_page",
        "views/page-privacy-policy.blade.php",
        "privacy-policy",
    );
}

/**
 * Get terms page content for use in modals, etc.
 *
 * @return string
 */
function growtype_form_get_signup_terms_page_content()
{
    return _growtype_form_get_signup_legal_page_content(
        "growtype_form_signup_terms_page",
        "views/page-terms-and-conditions.blade.php",
        "terms-and-conditions",
    );
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_profile_page_url()
{
    return home_url(Growtype_Form_Profile_Edit::URL_PATH);
}

/**
 * @return false|string|WP_Error|null
 * Custom signup url
 */
function growtype_form_profile_settings_page_url()
{
    return home_url(Growtype_Form_Profile_Settings::URL_PATH);
}

/**
 * @return array|WP_Post|null
 */
function growtype_form_default_redirect_after_signup_page()
{
    return get_option("growtype_form_redirect_after_signup_page", "default");
}

/**
 * @return false|string|WP_Error|null
 * Custom lost password url
 */
if (!function_exists("growtype_form_redirect_url_after_signup")) {
    function growtype_form_redirect_url_after_signup()
    {
        $redirect_page = growtype_form_default_redirect_after_signup_page();

        if (isset($_GET["redirect_after"]) && !empty($_GET["redirect_after"])) {
            $redirect_url = $_GET["redirect_after"];
        } elseif (
            isset($_SERVER["HTTP_REFERER"]) &&
            strpos($_SERVER["HTTP_REFERER"], "wp/wp-login") !== false
        ) {
            $redirect_url = get_dashboard_url();
        } elseif (isset($_COOKIE["growtype_form_redirect_after"])) {
            $redirect_url = $_COOKIE["growtype_form_redirect_after"];
        } elseif ($redirect_page === "dashboard") {
            $redirect_url = get_dashboard_url();
        } elseif ($redirect_page === "default-profile") {
            $redirect_url = growtype_form_profile_page_url();
        } elseif ($redirect_page === "none") {
            $redirect_url = growtype_form_profile_page_url();
        } else {
            $redirect_url = get_permalink($redirect_page);
        }

        return apply_filters(
            "growtype_form_redirect_url_after_signup",
            $redirect_url,
        );
    }
}
