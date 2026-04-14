<?php

/**
 * Handles custom meta boxes for gf_lead.
 */
class Growtype_Form_Admin_Lead_Meta_Boxes
{
    const META_BOXES = [
        [
            'id'     => 'lead_details',
            'title'  => 'Lead details',
            'fields' => []
        ]
    ];

    public static function get_meta_boxes()
    {
        $meta_boxes = self::META_BOXES;

        return apply_filters('growtype_form_lead_meta_boxes', $meta_boxes);
    }
    public function __construct()
    {
        add_action('add_meta_boxes', array ($this, 'add_custom_meta_boxes'));
        add_action('save_post', array ($this, 'save_custom_meta_boxes'));

        /**
         * Admin footer
         */
        add_action('admin_footer', array ($this, 'admin_footer_extend'));
    }

    function admin_footer_extend()
    {
        if (Growtype_Form_Admin_Lead_Crud::is_edit_post_type()) {
            Growtype_Form_Admin::init_json_editor('#events_log', [
                'height' => '600px'
            ]);
        }
    }

    public function add_custom_meta_boxes()
    {
        foreach (self::get_meta_boxes() as $box) {
            add_meta_box(
                $box['id'],
                $box['title'],
                array ($this, 'render_custom_meta_box'),
                Growtype_Form_Admin_Lead::POST_TYPE_NAME,
                'normal',
                'high',
                array ('fields' => $box['fields'])
            );
        }
    }

    public function render_custom_meta_box($post, $params)
    {
        ?>
        <div style="display: flex; gap: 10px; flex-direction: column;">
            <?php
            foreach ($params['args']['fields'] as $field) {
                $meta_value = get_post_meta($post->ID, $field['key'], true);
                $type = $field['type'] ?? 'text';
                ?>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <label style="min-width: 150px; max-width: 150px;" for="<?php echo esc_attr($field['key']); ?>">
                        <?php echo esc_html($field['title']); ?>:
                    </label>

                    <?php if ($type === 'events_log') {
                        $log_data = json_decode($meta_value, true) ?? [];
                        ?>
                        <div style="width: 100%;overflow:scroll;">
                            <div id="events-log-table-container">
                                <table class="widefat striped" style="margin-bottom: 10px;">
                                    <thead>
                                    <tr>
                                        <th style="width: 150px;">Date</th>
                                        <th style="width: 200px;">Action</th>
                                        <th style="width: 80px;">Status</th>
                                        <th>Key Details</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($log_data)): ?>
                                        <tr>
                                            <td colspan="4">No events logged yet.</td>
                                        </tr>
                                    <?php
                                    else: ?>
                                        <?php foreach (array_reverse($log_data) as $entry):
                                            $details = $entry['details'] ?? [];
                                            $success = isset($entry['success']) && ($entry['success'] === 'true' || $entry['success'] === true);
                                            ?>
                                            <tr>
                                                <td><?php echo esc_html($entry['date']); ?></td>
                                                <td>
                                                    <strong><?php echo esc_html($details['action'] ?? '-'); ?></strong>
                                                </td>
                                                <td>
                                                        <span style="color: <?php echo $success ? '#2271b1' : '#d63638'; ?>; font-weight: bold;">
                                                            <?php echo $success ? '✓' : '✗'; ?>
                                                        </span>
                                                </td>
                                                <td>
                                                    <div style="font-size: 11px; line-height: 1.4;">
                                                        <?php
                                                        foreach ($details as $k => $v):
                                                            if ($k === 'action') {
                                                                continue;
                                                            }
                                                            if (empty($v)) {
                                                                continue;
                                                            }
                                                            ?>
                                                            <span style="background: #f0f0f1; padding: 1px 4px; border-radius: 3px; margin-right: 5px; display: inline-block; margin-bottom: 2px;">
                                                                    <strong><?php echo esc_html($k); ?>
                                                                        :</strong> <?php echo esc_html(is_array($v) ? json_encode($v) : $v); ?>
                                                                </span>
                                                        <?php
                                                        endforeach; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        endforeach; ?>
                                    <?php
                                    endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="events-log-raw-container" style="display: none;">
                                <textarea
                                    style="width: 100%;"
                                    id="<?php echo esc_attr($field['key']); ?>"
                                    name="<?php echo esc_attr($field['key']); ?>"
                                    rows="5"
                                ><?php echo esc_textarea($meta_value); ?></textarea>
                            </div>
                            <button type="button" class="button" id="toggle-events-log-view">Switch to Raw View</button>
                            <script>
                                jQuery(document).ready(function ($) {
                                    $('#toggle-events-log-view').off('click').on('click', function () {
                                        $('#events-log-table-container, #events-log-raw-container').toggle();
                                        var isTableVisible = $('#events-log-table-container').is(':visible');
                                        $(this).text(isTableVisible ? 'Switch to Raw View' : 'Switch to Table View');

                                        if (!isTableVisible) {
                                            // Trigger resize for Ace editor when it becomes visible
                                            var editorElement = $('#<?php echo esc_js($field['key']); ?>');
                                            if (editorElement.data('ace')) {
                                                var aceInstance = editorElement.data('ace').editor.ace;
                                                $(aceInstance.container).css('width', '100%');
                                                aceInstance.resize();
                                                aceInstance.renderer.updateFull();
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>
                        <?php
                    } elseif ($type === 'textarea') { ?>
                        <textarea
                            style="width: 100%;"
                            id="<?php echo esc_attr($field['key']); ?>"
                            name="<?php echo esc_attr($field['key']); ?>"
                            rows="5"
                        ><?php echo esc_textarea($meta_value); ?></textarea>

                        <?php
                    } elseif ($type === 'checkbox') { ?>
                        <input
                            type="checkbox"
                            id="<?php echo esc_attr($field['key']); ?>"
                            name="<?php echo esc_attr($field['key']); ?>"
                            value="1"
                            <?php checked($meta_value, '1'); ?>
                        />

                        <?php
                    } else { ?>
                        <input
                            style="width: 100%;"
                            type="text"
                            id="<?php echo esc_attr($field['key']); ?>"
                            name="<?php echo esc_attr($field['key']); ?>"
                            value="<?php echo esc_attr($meta_value); ?>"
                        />
                        <?php
                    } ?>
                </div>
                <?php
            } ?>
        </div>
        <?php
    }

    public function save_custom_meta_boxes($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_type($post_id) !== Growtype_Form_Admin_Lead::POST_TYPE_NAME) {
            return;
        }

        foreach (self::get_meta_boxes() as $box) {
            foreach ($box['fields'] as $field) {
                $key = $field['key'];
                $type = $field['type'] ?? 'text';

                if ($type === 'checkbox') {
                    // Save 1 if checked, 0 if not set
                    $value = isset($_POST[$key]) ? '1' : '0';
                    update_post_meta($post_id, $key, $value);
                } else {
                    if (isset($_POST[$key])) {
                        $value = $_POST[$key];
                        if ($type === 'events_log' || $type === 'textarea') {
                            $value = sanitize_textarea_field($value);
                        } else {
                            $value = sanitize_text_field($value);
                        }
                        update_post_meta($post_id, $key, $value);
                    }
                }
            }
        }
    }
}
