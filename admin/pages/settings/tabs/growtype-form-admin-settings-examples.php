<?php

class Growtype_Form_Admin_Settings_Examples
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'admin_settings'));

        add_filter('growtype_form_admin_settings_tabs', array ($this, 'settings_tab'), 100);
    }

    function settings_tab($tabs)
    {
        $tabs['examples'] = 'Examples';

        return $tabs;
    }

    function admin_settings()
    {
        /**
         * Upload post
         */
        register_setting(
            'growtype_form_settings_examples', // settings group name
            'growtype_form_examples_json_content' // option name
        );

        add_settings_field(
            'growtype_form_examples_json_content',
            'Json Content',
            array ($this, 'growtype_form_examples_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_examples'
        );
    }

    /**
     * Upload post
     */
    function growtype_form_examples_json_content_callback()
    {
        $context_options = array (
            "ssl" => array (
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $json = file_get_contents(GROWTYPE_FORM_URL . 'admin/examples/login.json', false, stream_context_create($context_options)) . ',' .
            file_get_contents(GROWTYPE_FORM_URL . 'admin/examples/signup.json', false, stream_context_create($context_options)) . ',' .
            file_get_contents(GROWTYPE_FORM_URL . 'admin/examples/woocommerce.json', false, stream_context_create($context_options)) . ',' .
            file_get_contents(GROWTYPE_FORM_URL . 'admin/examples/post.json', false, stream_context_create($context_options));
        ?>
        <textarea id="growtype_form_examples_json_content" class="growtype_form_json_content" name="growtype_form_examples_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;"><?= $json ?></textarea>
        <?php
    }
}
