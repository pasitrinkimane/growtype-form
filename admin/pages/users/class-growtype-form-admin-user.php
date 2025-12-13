<?php

class Growtype_Form_Admin_User
{
    public function __construct()
    {
        // Show after personal options
        add_action('personal_options', [$this, 'admin_profile_picture_field']);

        // Save uploaded or removed picture
        add_action('personal_options_update', [$this, 'save_admin_profile_picture']);
        add_action('edit_user_profile_update', [$this, 'save_admin_profile_picture']);

        // Override WP avatar globally
        add_filter('get_avatar_url', [$this, 'override_avatar'], 10, 3);

        // Enable multipart/form-data for uploads
        add_action('user_edit_form_tag', [$this, 'add_multipart_support']);

        // Hide default WP profile picture (Gravatar)
        add_action('admin_head-user-edit.php', [$this, 'hide_default_wp_profile_picture']);
        add_action('admin_head-profile.php', [$this, 'hide_default_wp_profile_picture']);

        // Enqueue WordPress media scripts
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_media();
        });

        // AJAX for instant media selection save
        add_action('wp_ajax_update_profile_picture', [$this, 'ajax_update_profile_picture']);
    }

    public function hide_default_wp_profile_picture()
    {
        echo '
            <style>
                #profile-page .user-profile-picture,
                tr.user-profile-picture {
                    display: none !important;
                }
            </style>
        ';
    }

    public function add_multipart_support()
    {
        echo ' enctype="multipart/form-data"';
    }

    /**
     * Admin Form Field
     */
    public function admin_profile_picture_field($user)
    {
        $profile_picture = get_user_meta($user->ID, 'profile_picture', true);
        ?>
        <table class="form-table">
            <h2>Profile Picture</h2>
            <tr>
                <th><label for="profile_picture">Avatar Image</label></th>
                <td>
                    <div id="profile-picture-container" style="position: relative; display: inline-block;">
                        <img src="<?php echo esc_url($profile_picture ?: get_avatar_url($user->ID)); ?>"
                             style="width:100px;height:100px;border-radius:50%;margin-bottom:10px;">
                        <?php if ($profile_picture): ?>
                            <button type="button" id="remove_profile_picture"
                                    style="position:absolute;top:0;right:0;background:none;border:none;cursor:pointer;">
                                <span class="dashicons dashicons-trash"
                                      style="border-radius:20px;padding:5px;background:black;color:white;"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <br>
                    <button type="button" class="button" id="select_profile_picture">Select from Media Library</button>
                    <input type="hidden" name="profile_picture" id="profile_picture_hidden"
                           value="<?php echo esc_attr($profile_picture); ?>">
                </td>
            </tr>
        </table>
        <?php wp_nonce_field('growtype_form_profile_picture', 'growtype_form_profile_picture_nonce'); ?>

        <script>
            jQuery(document).ready(function ($) {
                var mediaUploader;

                $('#select_profile_picture').click(function (e) {
                    e.preventDefault();
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    mediaUploader = wp.media.frames.file_frame = wp.media({
                        title: 'Select Profile Picture',
                        button: {text: 'Use this image'},
                        multiple: false
                    });
                    mediaUploader.on('select', function () {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#profile_picture_hidden').val(attachment.url);
                        $('#profile-picture-container img').attr('src', attachment.url);

                        // AJAX save immediately
                        $.post(ajaxurl, {
                            action: 'update_profile_picture',
                            user_id: <?php echo $user->ID; ?>,
                            url: attachment.url,
                            nonce: '<?php echo wp_create_nonce('growtype_form_profile_picture'); ?>'
                        });
                    });
                    mediaUploader.open();
                });

                $('#remove_profile_picture').click(function () {
                    if (!confirm('Remove profile picture?')) return;

                    $.post(ajaxurl, {
                        action: 'update_profile_picture',
                        user_id: <?php echo $user->ID; ?>,
                        url: '',
                        remove: 1,
                        nonce: '<?php echo wp_create_nonce('growtype_form_profile_picture'); ?>'
                    }, function () {
                        $('#profile-picture-container img').attr('src', '<?php echo get_avatar_url(0); ?>');
                        $('#remove_profile_picture').remove();
                        $('#profile_picture_hidden').val('');
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Save Picture on Update (for normal form submission)
     */
    public function save_admin_profile_picture($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        // Handle hidden field from JS media selection
        if (!empty($_POST['profile_picture'])) {
            if (!isset($_POST['growtype_form_profile_picture_nonce']) || !wp_verify_nonce($_POST['growtype_form_profile_picture_nonce'], 'growtype_form_profile_picture')) {
                return;
            }
            update_user_meta($user_id, 'profile_picture', esc_url_raw($_POST['profile_picture']));
        }

        // Remove picture
        if (!empty($_POST['remove_profile_picture'])) {
            if (!isset($_POST['growtype_form_profile_picture_nonce']) || !wp_verify_nonce($_POST['growtype_form_profile_picture_nonce'], 'growtype_form_profile_picture')) {
                return;
            }
            $this->delete_profile_picture($user_id);
        }

        // Handle direct file upload
        if (!empty($_FILES['profile_picture']['name'])) {
            if (!isset($_POST['growtype_form_profile_picture_nonce']) || !wp_verify_nonce($_POST['growtype_form_profile_picture_nonce'], 'growtype_form_profile_picture')) {
                return;
            }
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload('profile_picture', 0);

            if (!is_wp_error($attachment_id)) {
                update_user_meta($user_id, 'profile_picture', wp_get_attachment_url($attachment_id));
            }
        }
    }

    /**
     * AJAX handler for instant media library selection
     */
    public function ajax_update_profile_picture()
    {
        $user_id = intval($_POST['user_id']);
        if (!current_user_can('edit_user', $user_id)) {
            wp_die('Unauthorized');
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'growtype_form_profile_picture')) {
            wp_die('Invalid nonce');
        }

        if (!empty($_POST['remove'])) {
            $this->delete_profile_picture($user_id);
        } elseif (!empty($_POST['url'])) {
            update_user_meta($user_id, 'profile_picture', esc_url_raw($_POST['url']));
        }

        wp_die();
    }

    /**
     * Delete profile picture from user meta and media library
     */
    private function delete_profile_picture($user_id)
    {
        delete_user_meta($user_id, 'profile_picture');
    }

    /**
     * Override WP Avatar
     */
    public function override_avatar($url, $id_or_email, $args)
    {
        $user = false;
        if (is_numeric($id_or_email)) {
            $user = get_user_by('id', $id_or_email);
        } elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
            $user = get_user_by('id', $id_or_email->user_id);
        } elseif (is_string($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
        }

        if ($user) {
            $custom = get_user_meta($user->ID, 'profile_picture', true);
            if (!empty($custom)) {
                return $custom;
            }
        }
        return $url;
    }
}
