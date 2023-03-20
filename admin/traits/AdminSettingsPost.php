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

trait AdminSettingsPost
{
    public function post_content()
    {
        /**
         * Upload post
         */
        register_setting(
            'growtype_form_settings_post', // settings group name
            'growtype_form_post_json_content' // option name
        );

        add_settings_field(
            'growtype_form_post_json_content',
            'Json Content',
            array ($this, 'growtype_form_post_json_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_post'
        );

        /**
         * Saving post title key
         */
        register_setting(
            'growtype_form_settings_post', // settings group name
            'growtype_form_settings_post_saving_post_title_name', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_settings_post_saving_post_title_name',
            'Post title name',
            array ($this, 'growtype_form_settings_post_saving_post_title_name_callback'),
            'growtype-form-settings',
            'growtype_form_settings_post_saving'
        );

        /**
         * Email to
         */
        register_setting(
            'growtype_form_settings_post', // settings group name
            'growtype_form_post_default_email_to', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_post_default_email_to',
            'Email to',
            array ($this, 'growtype_form_post_default_email_to_callback'),
            'growtype-form-settings',
            'growtype_form_settings_post_email'
        );

        /**
         * Email to subject
         */
        register_setting(
            'growtype_form_settings_post', // settings group name
            'growtype_form_post_default_email_to_subject', // option name
            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_post_default_email_to_subject',
            'Email to - subject',
            array ($this, 'growtype_form_post_default_email_to_subject_callback'),
            'growtype-form-settings',
            'growtype_form_settings_post_email'
        );

        /**
         * Email to content
         */
        register_setting(
            'growtype_form_settings_post', // settings group name
            'growtype_form_post_default_email_to_content', // option name
//            'sanitize_text_field' // sanitization function
        );

        add_settings_field(
            'growtype_form_post_default_email_to_content',
            'Email to - content',
            array ($this, 'growtype_form_post_default_email_to_content_callback'),
            'growtype-form-settings',
            'growtype_form_settings_post_email'
        );
    }

    /**
     * Upload post
     */
    function growtype_form_post_json_content_callback()
    {
        $json_content = get_option('growtype_form_post_json_content');

        if (empty($json_content)) {
            $context_options = array (
                "ssl" => array (
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );
            $json_content = file_get_contents(plugin_dir_url(__DIR__) . 'examples/post.json', false, stream_context_create($context_options));
        }

        ?>
        <textarea id="growtype_form_post_json_content" class="growtype_form_json_content" name="growtype_form_post_json_content" rows="40" cols="100" style="width: 100%;margin-bottom: 100px;"><?= $json_content ?></textarea>
        <?php
    }

    /**
     * Email to
     */
    function growtype_form_settings_post_saving_post_title_name_callback()
    {
        $growtype_form_settings_post_saving_post_title_name = !empty(get_option('growtype_form_settings_post_saving_post_title_name')) ? get_option('growtype_form_settings_post_saving_post_title_name') : 'title';
        ?>
        <input type="text" name="growtype_form_settings_post_saving_post_title_name" class="regular-text qtranxs-translatable" value="<?php echo $growtype_form_settings_post_saving_post_title_name ?>"/>
        <?php
    }

    /**
     * Email to
     */
    function growtype_form_post_default_email_to_callback()
    {
        $growtype_form_post_default_email_to = get_option('growtype_form_post_default_email_to');
        ?>
        <input type="email" name="growtype_form_post_default_email_to" class="regular-text qtranxs-translatable" value="<?php echo $growtype_form_post_default_email_to ?>"/>
        <?php
    }

    /**
     * Email to subject
     */
    function growtype_form_post_default_email_to_subject_callback()
    {
        $growtype_form_post_default_email_to_subject = !empty(get_option('growtype_form_post_default_email_to_subject')) ? get_option('growtype_form_post_default_email_to_subject') : 'Growtype Form - New post created';
        ?>
        <input type="text" name="growtype_form_post_default_email_to_subject" class="regular-text qtranxs-translatable" value="<?php echo $growtype_form_post_default_email_to_subject ?>"/>
        <?php
    }

    /**
     * Email to content
     */
    function growtype_form_post_default_email_to_content_callback()
    {
        $growtype_form_post_default_email_to_content = !empty(get_option('growtype_form_post_default_email_to_content')) ? get_option('growtype_form_post_default_email_to_content') : '<h2>New post submission,</h2></br> Below are the details of the post submission: </br> $post_content';

        wp_editor($growtype_form_post_default_email_to_content, 'growtype_form_post_default_email_to_content', array (
            'textarea_name' => 'growtype_form_post_default_email_to_content',
            'media_buttons' => false,
        ));
    }
}

