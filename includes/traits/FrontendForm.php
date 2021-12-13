<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Growtype_Form
 * @subpackage Growtype_Form/admin/partials
 */

trait FrontendForm
{
    /**
     * @param $form
     * @return false|string|null
     */
    function render_growtype_general_form($form)
    {
        /**
         * Form main fields
         */
        $main_fields = $form['main_fields'] ?? null;

        if (empty($main_fields)) {
            return null;
        }

        /**
         * Recaptcha setup
         */
        $recaptcha = $form['recaptcha'] ?? null;
        $recaptcha_key = $recaptcha['api_key'] ?? null;

        if (!empty($recaptcha_key) && !function_exists('recaptcha_setup')) {
            add_action('wp_footer', function () use (&$recaptcha_key) {
                $this->recaptcha_setup($recaptcha_key);
            }, 99);
        }

        /**
         * Form confirmation fields
         */
        $confirmation_fields = $form['confirmation_fields'] ?? null;

        /**
         * Form title
         */
        $form_title = $form['title'] ?? null;

        /**
         * Form title
         */
        $form_type = $form['type'] ?? null;

        /**
         * Login btn
         */
        $login_btn = $form['login_btn'] ?? null;

        ob_start();
        ?>
        <div class="growtype-form-wrapper">

            <?php $this->render_growtype_form_response_status(); ?>

            <div class="container growtype-form-container">
                <div class="form-wrapper">
                    <?php if (!empty($form_title)) { ?>
                        <h2 class="e-title-intro"><?= $form_title ?></h2>
                    <?php } ?>
                    <form id="growtype-form" <?= $form_type === 'upload' ? 'enctype="multipart/form-data"' : '' ?> class="form" action="<?php the_permalink(); ?>" method="post">
                        <div class="row g-3 fields-main">
                            <?php
                            foreach ($main_fields as $field) {
                                $this->render_growtype_form_field($field);
                            }
                            ?>
                        </div>

                        <?php
                        if (!empty($confirmation_fields)) { ?>
                            <div class="row fields-confirmation">
                                <?php
                                foreach ($confirmation_fields as $field) {
                                    $this->render_growtype_form_field($field);
                                }
                                ?>
                            </div>
                        <?php } ?>

                        <div class="row row-submit">
                            <div class="col-12">

                                <input type="text" hidden name='<?= self::GROWTYPE_FORM_SUBMITTED_INPUT ?>' value="true"/>
                                <input type="text" hidden name='<?= self::GROWTYPE_FORM_SUBMITTER_ID ?>' value="<?= get_current_user_id() ?? null ?>"/>

                                <?php if (!empty($recaptchav3)) { ?>
                                    <div class="g-recaptcha"
                                         data-sitekey="<?= $recaptchav3 ?>"
                                         data-size="invisible"
                                         data-callback="uploadFormSubmit">
                                    </div>
                                <?php } ?>

                                <button type="submit" class="btn btn-primary"><?= $form['submit_label'] ?? __("Save", "growtype-form") ?></button>
                            </div>
                        </div>
                    </form>

                    <div class="b-actions">
                        <?php
                        if ($login_btn) { ?>
                            <label for=""><?= __("You already have an account?", "growtype-registration") ?></label>
                            <a class="btn btn-link" href="<?= growtype_form_login_url() ?>"><?= __("Sign in", "growtype-registration") ?></a>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>

        <?php

        return ob_get_clean();
    }

    /**
     * @param $field
     * @return null
     */
    function render_growtype_form_field($field)
    {
        $field_name = $field['name'] ?? false;
        $field_required = isset($field['required']) && $field['required'] === 'true' ? true : false;
        $field_type = $field['type'];
        $field_hidden = $field['hidden'] ?? false;

        if ($field_type === 'hidden') {
            $field_hidden = true;
        }

        $field_value = $field['value'] ?? sanitize_text_field(filter_input(INPUT_GET, $field_name));

        $field_options = $field['options'] ?? null;
        $field_label = $field['label'] ?? null;
        $field_label = $field_required && !str_contains($field_label, '*') ? $field_label . '*' : $field_label;
        $field_description = $field['description'] ?? null;
        $placeholder = $field['placeholder'] ?? null;
        $field_accept = $field['accept'] ?? null;
        $field_min_value = $field['min'] ?? null;
        $field_max_value = $field['max'] ?? null;

        $field_col_class = $field['col_class'] ?? 'col-auto';

        if (!in_array($field_type, self::GROWTYPE_FORM_ALLOWED_FIELD_TYPES)) {
            return null;
        }

        ?>

        <div class="<?= $field_col_class ?>" style="<?= $field_hidden ? 'display:none;' : '' ?>" data-name="<?= $field_name ?>">
            <?php
            if ($field_type === 'select') { ?>
                <?php
                if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php }
                ?>
                <select name="<?= $field_name ?>" id="<?= $field_name ?>">
                    <?php
                    foreach ($field_options as $field_option) { ?>
                        <option value="<?= sanitize_text_field($field_option['value']) ?>"><?= sanitize_text_field($field_option['label']) ?></option>
                    <?php } ?>
                </select>
            <?php } elseif ($field_type === 'radio') { ?>
                <?php
                foreach ($field_options as $field_option) { ?>
                    <div class="radio-wrapper">
                        <input type="radio" id="<?= str_replace(' ', '_', strtolower($field_option)) ?>" name="<?= $field_name ?>" value="<?= strtolower($field_option) ?>" <?= $field_required ? 'required' : '' ?>>
                        <label for="<?= str_replace(' ', '_', strtolower($field_option)) ?>"><?= str_replace('_', ' ', $field_option) ?></label>
                    </div>
                <?php } ?>
            <?php } elseif ($field_type === 'checkbox') { ?>
                <div class="form-check">
                    <input type="<?= $field_type ?>"
                           class="form-check-input"
                           name="<?= $field_name ?>"
                           id="<?= $field_name ?>"
                           placeholder="<?= $placeholder ?>"
                        <?= $field_required ? 'required' : '' ?>
                           value="<?= !str_contains($field_name, 'password') ? $field_value : null ?>"
                    >
                    <?php
                    if (!empty($field_label)) { ?>
                        <label for="<?= $field_name ?>" class="form-label">
                            <?= $field_label ?>
                        </label>
                    <?php }
                    ?>
                </div>
            <?php } elseif ($field_type === 'textarea') { ?>
                <?php
                if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php }
                ?>
                <?php if (!empty($field_description)) { ?>
                    <p class="form-description"><?= $field_description ?></p>
                <?php } ?>
                <textarea id="<?= $field_name ?>" name="<?= $field_name ?>" rows="4" cols="50" placeholder="<?= $placeholder ?>" <?= $field_required ? 'required' : '' ?>></textarea>
            <?php } elseif ($field_type === 'file') { ?>
                <?php if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php } ?>
                <input type="<?= $field_type ?>" id="<?= $field_name ?>" name="<?= $field_name ?>" accept="<?= $field_accept ?>" <?= $field_required ? 'required' : '' ?>>
            <?php } else { ?>
                <?php
                if (!empty($field_label)) { ?>
                    <label for="<?= $field_name ?>" class="form-label">
                        <?= $field_label ?>
                    </label>
                <?php }
                ?>
                <?php if (!empty($field_description)) { ?>
                    <p class="form-description"><?= $field_description ?></p>
                <?php } ?>
                <input type="<?= $field_type ?>"
                       class="form-control"
                       name="<?= $field_name ?>"
                       id="<?= $field_name ?>"
                       placeholder="<?= $placeholder ?? null ?>"
                    <?= $field_required ? 'required' : '' ?>
                       value="<?= !str_contains($field_name, 'password') ? $field_value : null ?>"
                    <?= $field_min_value ? 'min="' . $field_min_value . '"' : '' ?>
                    <?= $field_max_value ? 'max="' . $field_max_value . '"' : '' ?>
                >
            <?php } ?>
        </div>

        <?php
    }

    /**
     *
     */
    function render_growtype_form_response_status()
    {
        $response_status = $_GET['status'] ?? null;

        if (!empty($response_status)) {

            $response_status_message = sanitize_text_field(filter_input(INPUT_GET, 'message'));

            if ($response_status === 'success') { ?>
                <div class="alert alert-success" role="alert">
                    <?= __($response_status_message, "growtype-form") ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger" role="alert">
                    <?= __($response_status_message, "growtype-form") ?>
                </div>
            <?php }
        }
    }

    /**
     * Required scripts
     */
    function growtype_form_enqueue_validation_scripts()
    {
        wp_enqueue_script('jquery.validate.js', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js', '', '', true);

        if (get_locale() === 'lt_LT') {
            wp_enqueue_script('jquery.validate.js.localization', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/localization/messages_lt.js', '', '', true);
        }
    }

    /**
     *
     */
    function growtype_form_url_scripts_init()
    {
        ?>
        <script>
            if (window.location.search.length > 0 && window.location.search.indexOf('action') !== -1) {
                window.history.replaceState(null, null, window.location.pathname);
            } else if (window.location.search.length > 0 && window.location.search.indexOf('message') !== -1) {
                window.growtypeWcUploadFormFailed = true;
                window.history.replaceState(null, null, window.location.pathname);
            }
        </script>
        <?php
    }

    /**
     * Validate form
     */
    function growtype_form_validation_scripts_init()
    {
        ?>
        <script>
            $.validator.setDefaults({ignore: ":hidden:not(select)"});

            if ($("#growtype-form select").length > 0) {
                $("#growtype-form select").each(function () {
                    if ($(this).attr('required') !== undefined) {
                        $(this).on("change", function () {
                            $(this).valid();
                        });
                    }
                });
            }

            $('#growtype-form button[type="submit"]').click(function () {
                var isValid = $("#growtype-form").valid();
                if (!isValid) {
                    event.preventDefault();
                }
            });

            $('#growtype-form').validate({
                errorPlacement: function (error, element) {
                    // console.log(element)
                    if (element.is("#growtype-form select")) {
                        element.closest(".col-auto").append(error);
                    } else if (element.is("#growtype-form input[type='checkbox']")) {
                        element.closest(".form-check").append(error);
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            $('#growtype-form').validate().settings.ignore = ".chosen-search-input";
        </script>
        <?php
    }

    /**
     * @param $recaptcha_key
     */
    function recaptcha_setup($recaptcha_key)
    {
        ?>
        <style>
            .grecaptcha-badge {
                display: none !important;
            }
        </style>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptcha_key ?>"></script>
        <script>
            $('#growtype-form').submit(function (event) {
                event.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                grecaptcha.reset();
                grecaptcha.execute();
            });

            function uploadFormSubmit(token) {
                document.getElementById("growtype-form").submit();
            }
        </script>
        <?php
    }
}

