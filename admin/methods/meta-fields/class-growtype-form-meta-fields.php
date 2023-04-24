<?php

abstract class Growtype_Form_Meta_Fields
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['post', 'product'];
        foreach ($screens as $screen) {
            add_meta_box(
                'growtype_form_settings',          // Unique ID
                'Growtype Form - Settings', // Box title
                [self::class, 'html'],   // Content callback, must be of type callable
                $screen                  // Post type
            );
        }
    }

    /**
     * Save the meta box selections.
     *
     * @param int $post_id The post ID.
     */
    public static function save(int $post_id)
    {
        if (array_key_exists('growtype_form_default_form_key', $_POST)) {
            update_post_meta(
                $post_id,
                '_growtype_form_default_form_key',
                $_POST['growtype_form_default_form_key']
            );
        }
    }

    /**
     * Display the meta box HTML to the user.
     *
     * @param WP_Post $post Post object.
     */
    public static function html($post)
    {
        $value = get_post_meta($post->ID, '_growtype_form_default_form_key', true);
        ?>
        <label for="growtype_form_default_form_key">Default form key</label>
        <input name="growtype_form_default_form_key" id="growtype_form_default_form_key" value="<?php echo $value ?>"/>
        <?php
    }
}

add_action('add_meta_boxes', ['Growtype_Form_Meta_Fields', 'add']);
add_action('save_post', ['Growtype_Form_Meta_Fields', 'save']);
