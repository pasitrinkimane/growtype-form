<div class="e-wrapper" data-required="<?php echo $field_required === true ? 'true' : 'false'?>">
    <?php
    echo preg_match('/^\[[a-zA-Z0-9_]+\]$/', $field['value']) ? do_shortcode($field['value']) : $field['value'];
    echo $field_description;
    ?>
</div>
