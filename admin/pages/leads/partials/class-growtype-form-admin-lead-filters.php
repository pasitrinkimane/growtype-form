<?php

/**
 * Handles custom filters for gf_lead.
 */
class Growtype_Form_Admin_Lead_Filters
{
    public function __construct()
    {
        add_action('admin_footer', array ($this, 'admin_footer_extend'));
    }

    function admin_footer_extend()
    {
        global $pagenow, $typenow;

        if ($typenow !== Growtype_Form_Admin_Lead::POST_TYPE_NAME || $pagenow !== 'edit.php') {
            return;
        }

        ?>
        <script>
            jQuery(document).ready(function ($) {
                var filterSelect = $('select[name="m"]');
                if (filterSelect.length > 0) {
                    var currentDate = new Date();
                    var optionsHtml = ''; // Collect options as HTML

                    // Generate options for the last 7 days, starting with today
                    for (let i = 0; i < 7; i++) {
                        var tempDate = new Date(currentDate);
                        tempDate.setDate(currentDate.getDate() - i);

                        // Format date as YYYY-MM-DD
                        var year = tempDate.getFullYear();
                        var month = ('0' + (tempDate.getMonth() + 1)).slice(-2);
                        var day = ('0' + tempDate.getDate()).slice(-2);
                        var formattedDate = year + '-' + month + '-' + day;

                        // Add labels for Today and Yesterday
                        var label = '';
                        if (i === 0) {
                            label = ' (Today)';
                        } else if (i === 1) {
                            label = ' (Yesterday)';
                        }

                        // Build the option HTML
                        optionsHtml += '<option value="' + formattedDate + '">' + formattedDate + label + '</option>';
                    }

                    // Prepend the options to the dropdown
                    filterSelect.prepend(optionsHtml);

                    // Handle select change
                    filterSelect.change(function () {
                        var selectedValue = $(this).val();
                        if (selectedValue) {
                            $('input[name="filter_by_day"]').val(selectedValue);
                        } else {
                            $('input[name="filter_by_day"]').val('');
                        }
                    });

                    // Get URL parameter
                    function getUrlParameter(name) {
                        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                        var results = regex.exec(window.location.search);
                        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                    }

                    // Set selected value from URL if present
                    var dayParam = getUrlParameter('m');
                    if (dayParam) {
                        filterSelect.val(dayParam).change();
                    }
                }
            });
        </script>
        <?php
    }
}
