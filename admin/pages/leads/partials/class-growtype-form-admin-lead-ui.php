<?php

/**
 * Handles shared UI components for gf_lead.
 */
class Growtype_Form_Admin_Lead_Ui
{
    public function __construct()
    {
        add_action('admin_head-edit.php', array ($this, 'render_shared_actions_bar'), 5);
    }

    public function render_shared_actions_bar()
    {
        global $typenow;

        if ($typenow !== Growtype_Form_Admin_Lead::POST_TYPE_NAME) {
            return;
        }
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var $actionsBar = $('.leads-actions-bar');
                if ($actionsBar.length === 0) {
                    $actionsBar = $('<div class="leads-actions-bar" style="display: inline-flex; gap: 5px; align-items: center; position: relative; top: -10px; margin-left: 5px;"></div>');
                    $actionsBar.insertBefore($('.wp-header-end'));
                }
            });
        </script>
        <?php
    }
}
