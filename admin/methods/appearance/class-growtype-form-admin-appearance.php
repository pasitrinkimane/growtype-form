<?php

class Growtype_Form_Admin_Appearance
{
    public function __construct()
    {
        add_action('admin_init', array ($this, 'add_nav_menu_meta_box'));
    }

    /**
     * Adds the meta box container in appearance menus
     */
    public function add_nav_menu_meta_box()
    {
        add_meta_box(
            'growtype_form_nav_manu',
            'Growtype Form',
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
                'ID' => 'login',
                'object_id' => 'login',
                'type_label' => 'Login',
                'title' => 'Login',
                'url' => '/' . Growtype_Form_Login::URL_PATH,
                'type' => 'custom',
                'object' => 'login',
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
                'ID' => 'register',
                'object_id' => 'register',
                'type_label' => 'Register',
                'title' => 'Register',
                'url' => '/' . Growtype_Form_Signup::URL_PATH,
                'type' => 'custom',
                'object' => 'register',
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
                'ID' => 'logout',
                'object_id' => 'logout',
                'type_label' => 'Logout',
                'title' => 'Logout',
                'url' => '#growtype_form_logout_url#',
                'type' => 'custom',
                'object' => 'logout',
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
                'ID' => 'profile',
                'object_id' => 'profile',
                'type_label' => 'Profile',
                'title' => 'Profile',
                'url' => '/' . Growtype_Form_Profile::URL_PATH,
                'type' => 'custom',
                'object' => 'profile',
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
                'ID' => 'profile-settings',
                'object_id' => 'profile-settings',
                'type_label' => 'Profile - Settings',
                'title' => 'Profile - Settings',
                'url' => '/' . Growtype_Form_Profile_Settings::URL_PATH,
                'type' => 'custom',
                'object' => 'profile-settings',
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
