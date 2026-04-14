<?php

/**
 * Handles all CSV export callbacks for gf_lead post type.
 */
class Growtype_Form_Admin_Lead_Export
{
    public function __construct()
    {
        $this->load_methods();

        add_action('admin_post_growtype_form_admin_export_emails', array ($this, 'export_emails_callback'));
        add_action('admin_post_growtype_form_admin_export_validated_emails', array ($this, 'export_validated_emails_callback'));
        add_action('admin_post_growtype_form_admin_export_leads', array ($this, 'export_leads_callback'));
        add_action('admin_post_growtype_form_admin_export_purchased_emails', array ($this, 'export_purchased_emails_callback'));
        add_action('admin_post_growtype_form_admin_export_customers', array ($this, 'export_customers_callback'));
        add_action('admin_head-edit.php', array ($this, 'render_scripts'));
    }

    public function load_methods()
    {
        /**
         * Include actions
         */
        include_once 'actions/class-growtype-form-admin-lead-export-emails.php';
        include_once 'actions/class-growtype-form-admin-lead-export-validated-emails.php';
        include_once 'actions/class-growtype-form-admin-lead-export-leads.php';
        include_once 'actions/class-growtype-form-admin-lead-export-purchased-emails.php';
        include_once 'actions/class-growtype-form-admin-lead-export-customers.php';
    }

    function render_scripts()
    {
        global $typenow;

        if ($typenow !== Growtype_Form_Admin_Lead::POST_TYPE_NAME) {
            return;
        }
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var $actionsBar = $('.leads-actions-bar');

                var $exportDropdown = $('<div class="export-dropdown" style="display: inline-block; position: relative; vertical-align: middle;">' +
                    '<button class="button button-primary">Export ▼</button>' +
                    '<ul class="export-main-menu" style="display:none; position:absolute; top:100%; left:0; background:#fff; border:1px solid #ccc; list-style:none; padding:0; margin:0; min-width:170px; z-index:9999;">' +
                    '<li data-action="emails" style="padding:8px; cursor:pointer; border-bottom: 1px solid #eee;">Export all emails</li>' +
                    '<li data-action="validated" style="padding:8px; cursor:pointer; border-bottom: 1px solid #eee;">Export validated emails</li>' +
                    '<li data-action="purchased" style="padding:8px; cursor:pointer; border-bottom: 1px solid #eee;">Export purchased emails</li>' +
                    '<li class="has-submenu" style="padding:8px; cursor:pointer; border-bottom: 1px solid #eee; position:relative;">' +
                    'Export Customers list <span style="float:right;">▶</span>' +
                    '<ul class="export-sub-menu" style="display:none; position:absolute; top:0; left:100%; background:#fff; border:1px solid #ccc; list-style:none; padding:0; margin:0; min-width:150px; z-index:10000; margin-left: 1px;">' +
                    '<li data-sub-action="facebook" style="padding:8px; cursor:pointer; border-bottom: 1px solid #eee;">Facebook</li>' +
                    '<li data-sub-action="xai" style="padding:8px; cursor:pointer;">XAI</li>' +
                    '</ul>' +
                    '</li>' +
                    '<li data-action="full" style="padding:8px; cursor:pointer;">Export all data</li>' +
                    '</ul>' +
                    '</div>');
                
                $actionsBar.prepend($exportDropdown);

                // Toggle dropdown
                $('.export-dropdown button').on('click', function (e) {
                    e.preventDefault();
                    $('.export-main-menu').toggle();
                });

                // Hover for sub-menu
                $('.export-dropdown .has-submenu').on('mouseenter', function () {
                    $(this).find('.export-sub-menu').show();
                }).on('mouseleave', function () {
                    $(this).find('.export-sub-menu').hide();
                });

                // Export emails
                $('.export-dropdown ul li[data-action="emails"]').on('click', function () {
                    window.location.href = '<?php echo admin_url('admin-post.php?action=growtype_form_admin_export_emails'); ?>';
                });

                // Export validated emails
                $('.export-dropdown ul li[data-action="validated"]').on('click', function () {
                    window.location.href = '<?php echo admin_url('admin-post.php?action=growtype_form_admin_export_validated_emails'); ?>';
                });

                // Export full
                $('.export-dropdown ul li[data-action="full"]').on('click', function () {
                    window.location.href = '<?php echo admin_url('admin-post.php?action=growtype_form_admin_export_leads'); ?>';
                });

                // Export purchased
                $('.export-dropdown ul li[data-action="purchased"]').on('click', function () {
                    window.location.href = '<?php echo admin_url('admin-post.php?action=growtype_form_admin_export_purchased_emails'); ?>';
                });

                // Export customers facebook
                $('.export-dropdown .export-sub-menu li[data-sub-action="facebook"]').on('click', function (e) {
                    e.stopPropagation();
                    window.location.href = '<?php echo admin_url('admin-post.php?action=growtype_form_admin_export_customers&type=facebook'); ?>';
                });

                // Export customers XAI
                $('.export-dropdown .export-sub-menu li[data-sub-action="xai"]').on('click', function (e) {
                    e.stopPropagation();
                    window.location.href = '<?php echo admin_url('admin-post.php?action=growtype_form_admin_export_customers&type=xai'); ?>';
                });

                // Hide dropdown on click outside
                $(document).on('click', function (e) {
                    if (!$(e.target).closest('.export-dropdown').length) {
                        $('.export-main-menu').hide();
                    }
                });
            });
        </script>
        <?php
    }

    function export_emails_callback()
    {
        Growtype_Form_Admin_Lead_Export_Emails::run();
    }

    function export_validated_emails_callback()
    {
        Growtype_Form_Admin_Lead_Export_Validated_Emails::run();
    }

    function export_leads_callback()
    {
        Growtype_Form_Admin_Lead_Export_Leads::run();
    }

    function export_purchased_emails_callback()
    {
        Growtype_Form_Admin_Lead_Export_Purchased_Emails::run();
    }

    function export_customers_callback()
    {
        Growtype_Form_Admin_Lead_Export_Customers::run();
    }
}
