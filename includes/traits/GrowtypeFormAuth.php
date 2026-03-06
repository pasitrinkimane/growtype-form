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
                jQuery('.growtype-form-wrapper input[type="password"]').each(function() {
                    if (jQuery(this).next('.show-hide-pw').length === 0) {
                        jQuery('<button class="far fa-eye-slash show-hide-pw" type="button">&nbsp;</button>').insertAfter(jQuery(this));
                    }
                });
                
                jQuery(document).off('click', '.growtype-form-wrapper .show-hide-pw').on('click', '.growtype-form-wrapper .show-hide-pw', function () {
                    var $btn = jQuery(this);
                    var $input = $btn.parent().find('input');
                    
                    if ($btn.hasClass(close)) {
                        $input.prop('type', 'text');
                        $btn.removeClass(close).addClass(open);
                    } else {
                        $input.prop('type', 'password');
                        $btn.removeClass(open).addClass(close);
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
