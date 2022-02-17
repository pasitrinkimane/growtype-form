<?php if (!empty($field_label)) { ?>
    <label for="<?= $field_name ?>" class="form-label">
        <?= $field_label ?>
    </label>
<?php } ?>

<?php

$selected_options = [];

if (isset($_REQUEST[$field_name])) {
    $selected_options = is_array($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : explode(',', $_REQUEST[$field_name]);
} elseif (str_contains($field_name, '[')) {
    $cat_p = explode('[', $field_name)[0] ?? null;
    $cat_c = explode('[', $field_name)[1] ?? null;
    $cat_c = str_replace(']', '', $cat_c);
    $selected_options = $_REQUEST[$cat_p][$cat_c] ?? [];

    if (empty($selected_options) && isset($_REQUEST['repeater'])) {
        $selected_options = $_REQUEST['repeater'][$cat_p][$cat_c] ?? [];
    }
}

if (!is_array($selected_options)) {
    $selected_options = [$selected_options];
}

?>

<select name="<?= $field_name ?>" id="<?= $field_name ?>" <?= $field_required ? 'required' : '' ?> <?= $field_multiple ? 'multiple' : '' ?>>
    <?php

    if (isset($select_type) && $select_type === 'custom') {
        foreach ($field_options as $key => $field_option) { ?>
            <option value="<?= $key ?>" <?= in_array($key, $selected_options) ? 'selected' : '' ?>><?= $field_option ?></option>
        <?php }
    } else {
        foreach ($field_options as $field_option) { ?>
            <option value="<?= sanitize_text_field($field_option['value']) ?>" <?= in_array($field_option['value'], $selected_options) ? 'selected' : '' ?>><?= sanitize_text_field($field_option['label']) ?></option>
        <?php }
    } ?>
</select>
