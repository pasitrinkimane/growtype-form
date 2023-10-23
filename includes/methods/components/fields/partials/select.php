<select name="<?= $field_name ?>"
        data-placeholder="<?php echo isset($placeholder) ? $placeholder : '' ?>"
        id="<?= $field_name ?>"
    <?= $field_required ? 'required' : '' ?>
    <?= $field_multiple ? 'multiple' : '' ?>
>
    <?php if (!$field_multiple && isset($placeholder) && !empty($placeholder)) { ?>
        <option value="" <?php echo empty($selected_options) ? 'selected' : '' ?> disabled hidden <?= in_array('', $selected_options) ? 'selected' : '' ?>><?= $placeholder ?></option>
    <?php } ?>
    <?php if (isset($select_type) && $select_type === 'key_value') {
        foreach ($field_options as $key => $field_option) { ?>
            <option value="<?= $key ?>" <?= in_array($key, $selected_options) ? 'selected' : '' ?>><?= $field_option ?></option>
        <?php }
    } else {
        foreach ($field_options as $field_option) { ?>
            <option value="<?= sanitize_text_field($field_option['value']) ?>" <?= in_array($field_option['value'], $selected_options) ? 'selected' : '' ?>><?= sanitize_text_field($field_option['label']) ?></option>
        <?php }
    } ?>
</select>
