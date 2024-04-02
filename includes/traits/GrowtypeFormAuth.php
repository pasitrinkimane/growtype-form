<?php

/**
 *
 */
trait GrowtypeFormAuth
{
    public function growtype_form_show_hide_password_button()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                var open = 'fa-eye';
                var close = 'fa-eye-slash';
                var ele = jQuery('.growtype-form-wrapper input[type="password"]');
                jQuery('<button class="far fa-eye-slash show-hide-pw" type="button">&nbsp;</button>').insertAfter(ele);
                jQuery('.growtype-form-wrapper .show-hide-pw').click(function () {
                    if (jQuery(this).hasClass(close)) {
                        jQuery(this).parent().find('input[type="password"]').prop('type', 'text');
                        jQuery(this).removeClass(close);
                        jQuery(this).addClass(open);
                    } else {
                        jQuery(this).parent().find('input[type="text"]').prop('type', 'password');
                        jQuery(this).removeClass(open);
                        jQuery(this).addClass(close);
                    }
                });
            });
        </script>
        <style>
            .growtype-form-wrapper .show-hide-pw {
                background: none;
                border: none;
                position: absolute;
                right: 10px;
                top: 45px;
                opacity: 0.8;
                width: 40px;
                text-align: center;
            }
        </style>
        <?php
    }
}
