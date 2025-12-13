<?php

/**
 *
 */
class Growtype_Form_Profile_Roles
{
    public function __construct()
    {
        add_action('init', array ($this, 'add_custom_roles'));
    }

    function add_custom_roles()
    {
        /**
         * Lead role
         */
        add_role(
            'lead',
            __('Lead'),
            array (
                'read' => false,
                'delete_posts' => false,
                'delete_published_posts' => false,
                'edit_posts' => false,
                'publish_posts' => false,
                'edit_published_posts' => false,
                'upload_files' => false,
                'moderate_comments' => false,
            )
        );
    }
}
