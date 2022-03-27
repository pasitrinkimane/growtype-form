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

trait AdminSettingsExamples
{
    public function examples_content()
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
        $json = file_get_contents(plugin_dir_url(__DIR__) . 'examples/login.json') . ',' .
            file_get_contents(plugin_dir_url(__DIR__) . 'examples/signup.json') . ',' .
            file_get_contents(plugin_dir_url(__DIR__) . 'examples/woocommerce.json') . ',' .
            file_get_contents(plugin_dir_url(__DIR__) . 'examples/post.json');
        ?>
        <textarea id="growtype_form_examples_json_content" class="growtype_form_json_content" name="growtype_form_examples_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;"><?= $json ?></textarea>
        <?php
    }
}

