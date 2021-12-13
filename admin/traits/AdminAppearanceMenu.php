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

trait AdminAppearanceMenu
{
    /**
     * Adds the meta box container.
     */
    public function add_nav_menu_meta_box($post_type)
    {
        add_meta_box(
            'growtype_form_nav_manu',
            __('Growtype Form', 'growtype-form'),
            array ($this, 'render_nav_menu_meta_box_content'),
            'nav-menus',
            'side',
            'low'
        );
    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    function render_nav_menu_meta_box_content()
    {
        global $nav_menu_selected_id;

        $my_items = array (
            (object)array (
                'ID' => 1,
                'object_id' => 1,
                'type_label' => 'Login',
                'title' => 'Login',
                'url' => wp_login_url(),
                'type' => 'custom',
                'object' => 'plugin-slug-slug',
                'db_id' => 0,
                'menu_item_parent' => 0,
                'post_parent' => 0,
                'target' => '',
                'attr_title' => '',
                'description' => '',
                'classes' => array (),
                'xfn' => '',
            ),
            (object)array (
                'ID' => 1,
                'object_id' => 1,
                'type_label' => 'Logout',
                'title' => 'Logout',
                'url' => wp_logout_url(),
                'type' => 'custom',
                'object' => 'plugin-slug-slug',
                'db_id' => 0,
                'menu_item_parent' => 0,
                'post_parent' => 0,
                'target' => '',
                'attr_title' => '',
                'description' => '',
                'classes' => array (),
                'xfn' => '',
            ),
        );

        $db_fields = false;

        if (false) {
            $db_fields = array ('parent' => 'parent', 'id' => 'post_parent');
        }

        $walker = new Walker_Nav_Menu_Checklist($db_fields);
        ?>
        <div id="plugin-slug-div">
        <div id="tabs-panel-plugin-slug-all" class="tabs-panel tabs-panel-active">
            <ul id="plugin-slug-checklist-pop" class="categorychecklist form-no-clear">
                <?php echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $my_items), 0, (object)array ('walker' => $walker)); ?>
            </ul>
            <div class="button-controls">
                <div class="add-to-menu">
                    <input type="submit"<?php wp_nav_menu_disabled_check($nav_menu_selected_id); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-plugin-slug-menu-item" id="submit-plugin-slug-div"/>
                    <span class="spinner"></span>
                </div>
            </div>
        </div>
        <?php
    }
}

