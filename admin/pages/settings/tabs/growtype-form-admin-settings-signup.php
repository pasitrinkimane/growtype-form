<?php

class Growtype_Form_Admin_Settings_Signup
{
    const TAB_SLUG = "signup";

    public function __construct()
    {
        add_action("admin_init", [$this, "admin_settings"]);

        add_filter(
            "growtype_form_admin_settings_tabs",
            [$this, "settings_tab"],
            40,
        );
    }

    function settings_tab($tabs)
    {
        $tabs[self::TAB_SLUG] = "Signup";

        return $tabs;
    }

    function admin_settings()
    {
        add_settings_section(
            "growtype_form_settings_signup_general_section_id",
            "General",
            function () {
                echo "<p>Signup form settings</p>";
            },
            "growtype_form_settings_signup_general_section",
        );

        /**
         * growtype_form_json_content
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_json_content", // option name
        );

        add_settings_field(
            "growtype_form_signup_json_content",
            "Json Content",
            [$this, "growtype_form_signup_json_content_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Redirect after signup
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_redirect_after_signup_page", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_redirect_after_signup_page",
            '<span style="color: orange;">Redirect After Signup To</span>',
            [$this, "growtype_form_redirect_after_signup_page_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Default user role
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_default_user_role", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_default_user_role",
            '<span style="color: orange;">Default User Role</span>',
            [$this, "growtype_form_default_user_role_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Active user role
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_active_user_role", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_active_user_role",
            '<span style="color: orange;">Active User Role (when user requires validation)</span>',
            [$this, "growtype_form_active_user_role_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Signup page
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_page",
        );

        add_settings_field(
            "growtype_form_signup_page",
            "Signup Page",
            [$this, "growtype_form_signup_page_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Signup page template
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_page_template", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_signup_page_template",
            "Signup Page Template",
            [$this, "growtype_form_signup_page_template_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Allow simple password
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_allow_simple_password", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_allow_simple_password",
            "Allow Simple Password",
            [$this, "growtype_form_allow_simple_password_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Terms page
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_terms_page",
        );

        add_settings_field(
            "growtype_form_signup_terms_page",
            '"Terms And Conditions" Page',
            [$this, "growtype_form_signup_terms_page_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Privacy page
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_privacy_page",
        );

        add_settings_field(
            "growtype_form_signup_privacy_page",
            '"Privacy policy" Page',
            [$this, "growtype_form_signup_privacy_page_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Show footer
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_show_footer", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_signup_show_footer",
            "Show Footer in Signup page",
            [$this, "growtype_form_signup_show_footer_callback"],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         * Platform page
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_account_verification_platform_page",
        );

        add_settings_field(
            "growtype_form_account_verification_platform_page",
            "Platform Page (Main page after account verification to redirect)",
            [
                $this,
                "growtype_form_account_verification_platform_page_callback",
            ],
            "growtype_form_settings_signup_general_section",
            "growtype_form_settings_signup_general_section_id",
        );

        /**
         *
         */
        add_settings_section(
            "growtype_form_settings_signup_email_confirmation_section_id",
            "Confirmation",
            function () {
                echo "<p>Signup confirmation settings</p>";
            },
            "growtype_form_settings_signup_email_confirmation_section",
        );

        /**
         * Signup requires manual confirmation
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_signup_requires_manual_confirmation", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_signup_requires_manual_confirmation",
            "Signup requires MANUAL confirmation",
            [
                $this,
                "growtype_form_signup_requires_manual_confirmation_callback",
            ],
            "growtype_form_settings_signup_email_confirmation_section",
            "growtype_form_settings_signup_email_confirmation_section_id",
        );

        /**
         * Signup requires email confirmation
         */
        register_setting(
            "growtype_form_settings_signup",
            "growtype_form_signup_requires_email_confirmation",
            "sanitize_text_field",
        );

        add_settings_field(
            "growtype_form_signup_requires_email_confirmation",
            "Signup requires EMAIL confirmation",
            [
                $this,
                "growtype_form_signup_requires_email_confirmation_callback",
            ],
            "growtype_form_settings_signup_email_confirmation_section",
            "growtype_form_settings_signup_email_confirmation_section_id",
        );

        /**
         * Add Users section
         */
        add_settings_section(
            "growtype_form_settings_signup_users_section_id",
            "Users",
            function () {
                echo "<p>Users signup settings</p>";
            },
            "growtype_form_settings_signup_users_section",
        );

        /**
         * Signups enabled
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_settings_signups_enabled", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_settings_signups_enabled",
            "Users signups table enabled",
            [$this, "growtype_form_settings_signups_enabled_callback"],
            "growtype_form_settings_signup_users_section",
            "growtype_form_settings_signup_users_section_id",
        );

        /**
         * Add Users section
         */
        add_settings_section(
            "growtype_form_settings_signup_onboarding_section_id",
            "Onboarding",
            function () {
                echo "<p>Onboarding settings</p>";
            },
            "growtype_form_settings_signup_onboarding_section",
        );

        /**
         * Signups enabled
         */
        register_setting(
            "growtype_form_settings_signup", // settings group name
            "growtype_form_settings_signups_onboarding_enabled", // option name
            "sanitize_text_field", // sanitization function
        );

        add_settings_field(
            "growtype_form_settings_signups_onboarding_enabled",
            "Signups onboarding enabled",
            [
                $this,
                "growtype_form_settings_signups_onboarding_enabled_callback",
            ],
            "growtype_form_settings_signup_onboarding_section",
            "growtype_form_settings_signup_onboarding_section_id",
        );
    }

    /**
     * General form
     */
    function growtype_form_signup_json_content_callback()
    {
        $json_content = get_option("growtype_form_signup_json_content");

        if (empty($json_content)) {
            $context_options = [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ];
            $json_content = file_get_contents(
                GROWTYPE_FORM_URL . "admin/examples/signup.json",
                false,
                stream_context_create($context_options),
            );
        }
        ?>
        <textarea id="growtype_form_signup_json_content" class="growtype_form_json_content" name="growtype_form_signup_json_content" rows="40" cols="100" style="width: 100%;"><?= $json_content ?></textarea>
        <?php
    }

    /**
     * Register page
     */
    function growtype_form_signup_page_callback()
    {
        $selected = growtype_form_signup_page_id();
        $pages = get_pages();
        ?>
        <select name='growtype_form_signup_page'>
            <option value='none' <?php selected(
                $selected,
                "none",
            ); ?>>None</option>
            <option value='default' <?php selected(
                $selected,
                "default",
            ); ?>>Default - Growtype Form</option>
            <?php foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected(
    $selected,
    $page->ID,
); ?>><?= __($page->post_title, "growtype-form") ?> - Page</option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Terms page
     */
    private function render_page_select($option_name)
    {
        $selected = get_option($option_name, "default_template");
        $pages = get_pages();
        ?>
        <select name='<?= $option_name ?>'>
            <option value='none' <?php selected(
                $selected,
                "none",
            ); ?>>None</option>
            <option value='default_template' <?php selected(
                $selected,
                "default_template",
            ); ?>>Default Template</option>
            <?php foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected(
    $selected,
    $page->ID,
); ?>><?= __($page->post_title, "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    function growtype_form_signup_terms_page_callback()
    {
        $this->render_page_select("growtype_form_signup_terms_page");
    }

    function growtype_form_signup_privacy_page_callback()
    {
        $this->render_page_select("growtype_form_signup_privacy_page");
    }

    /**
     * Login page template
     */
    function growtype_form_signup_page_template_callback()
    {
        $selected = growtype_form_get_signup_page_template();
        $options = ["template-default", "template-wide", "template-2"];
        ?>
        <select name='growtype_form_signup_page_template'>
            <?php foreach ($options as $option) { ?>
                <option value='<?= $option ?>' <?php selected(
    $selected,
    $option,
); ?>><?= $option ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Register page
     */
    function growtype_form_redirect_after_signup_page_callback()
    {
        $selected = growtype_form_default_redirect_after_signup_page();
        $pages = get_pages();
        ?>
        <select name='growtype_form_redirect_after_signup_page'>
            <option value='none' <?php selected(
                $selected,
                "none",
            ); ?>>None</option>
            <option value='default' <?php selected(
                $selected,
                "default",
            ); ?>>Default profile page - Growtype Form</option>
            <?php foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected(
    $selected,
    $page->ID,
); ?>><?= __($page->post_title, "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * User role
     */
    function growtype_form_default_user_role_callback()
    {
        global $wp_roles;

        $selected = get_option("growtype_form_default_user_role", "subscriber");
        $selected = !empty($selected) ? $selected : get_option("default_role");
        $roles = $wp_roles->roles;
        ?>
        <select name='growtype_form_default_user_role'>
            <?php foreach ($roles as $role => $role_details) { ?>
                <option value='<?= $role ?>' <?php selected(
    $selected,
    $role,
); ?>><?= __($role_details["name"], "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * User role
     */
    function growtype_form_active_user_role_callback()
    {
        global $wp_roles;

        $selected = get_option("growtype_form_active_user_role", "subscriber");
        $selected = !empty($selected) ? $selected : get_option("default_role");
        $roles = $wp_roles->roles;
        ?>
        <select name='growtype_form_active_user_role'>
            <?php foreach ($roles as $role => $role_details) { ?>
                <option value='<?= $role ?>' <?php selected(
    $selected,
    $role,
); ?>><?= __($role_details["name"], "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Allow simple password for signup
     */
    function growtype_form_allow_simple_password_callback()
    {
        $enabled = get_option("growtype_form_allow_simple_password"); ?>
        <input type="checkbox" name="growtype_form_allow_simple_password" value="1" <?php echo checked(
            1,
            $enabled,
            false,
        ); ?> />
        <?php
    }

    /**
     * Show footer
     */
    function growtype_form_signup_show_footer_callback()
    {
        $enabled = get_option("growtype_form_signup_show_footer"); ?>
        <input type="checkbox" name="growtype_form_signup_show_footer" value="1" <?php echo checked(
            1,
            $enabled,
            false,
        ); ?> />
        <?php
    }

    /**
     * Manual confirmation
     */
    function growtype_form_signup_requires_manual_confirmation_callback()
    {
        $enabled = get_option(
            "growtype_form_signup_requires_manual_confirmation",
        ); ?>
        <input type="checkbox" name="growtype_form_signup_requires_manual_confirmation" value="1" <?php echo checked(
            1,
            $enabled,
            false,
        ); ?> />
        <?php
    }

    /**
     * Email confirmation
     */
    function growtype_form_signup_requires_email_confirmation_callback()
    {
        $enabled = get_option(
            "growtype_form_signup_requires_email_confirmation",
        ); ?>
        <input type="checkbox" name="growtype_form_signup_requires_email_confirmation" value="1" <?php echo checked(
            1,
            $enabled,
            false,
        ); ?> />
        <?php
    }

    /**
     * Platform page
     */
    function growtype_form_account_verification_platform_page_callback()
    {
        $selected = get_option(
            "growtype_form_account_verification_platform_page",
        );
        $pages = get_pages();
        ?>
        <select name='growtype_form_account_verification_platform_page'>
            <option value='none' <?php selected(
                $selected,
                "none",
            ); ?>>None</option>
            <?php foreach ($pages as $page) { ?>
                <option value='<?= $page->ID ?>' <?php selected(
    $selected,
    $page->ID,
); ?>><?= __($page->post_title, "growtype-form") ?></option>
            <?php } ?>
        </select>
        <?php
    }

    function growtype_form_settings_signups_enabled_callback()
    {
        $enabled = get_option("growtype_form_settings_signups_enabled"); ?>
        <input type="checkbox" name="growtype_form_settings_signups_enabled" value="1" <?php echo checked(
            1,
            $enabled,
            false,
        ); ?> />
        <?php
    }

    function growtype_form_settings_signups_onboarding_enabled_callback()
    {
        $enabled = get_option(
            "growtype_form_settings_signups_onboarding_enabled",
        ); ?>
        <input type="checkbox" name="growtype_form_settings_signups_onboarding_enabled" value="1" <?php echo checked(
            1,
            $enabled,
            false,
        ); ?> />
        <?php
    }
}
