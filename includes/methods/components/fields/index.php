<?php
$field = apply_filters('growtype_form_field', $field);

$field_name = $field['name'] ?? false;
$field_required = isset($field['required']) && $field['required'] === 'true' ? true : false;
$field_multiple = isset($field['multiple']) && $field['multiple'] === 'true' ? true : false;
$field_type = isset($field['type']) ? $field['type'] : 'text';
$field_hidden = $field['hidden'] ?? false;

if ($field_type === 'hidden') {
    $field_hidden = true;
}

$field_value = isset($field['value']) ? sanitize_text_field($field['value']) : '';

$request_field_value = isset($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : '';
$request_field_value = $field_name === 'name' && isset($_REQUEST[Growtype_Form_Crud::ALTERNATIVE_SUBMITTED_DATA_KEYS[$field_name]]) ? $_REQUEST[Growtype_Form_Crud::ALTERNATIVE_SUBMITTED_DATA_KEYS[$field_name]] : $request_field_value;

if (empty($field_value)) {
    $field_value = !empty($request_field_value) && $field_type !== 'file' ? sanitize_text_field($request_field_value) : '';
}

if (str_contains($field_name, 'password')) {
    $field_value = null;
}

$field_options = isset($field['options']) ? $field['options'] : [];

$field_params = isset($field['params']) ? $field['params'] : [];
$field_params = json_encode($field_params);

$selected_options = isset($field['selected_options']) ? $field['selected_options'] : [$field_value];
$select_type = isset($field['select_type']) ? $field['select_type'] : null;
$field_label = isset($field['label']) ? $field['label'] : null;
$field_label = !empty($field_label) && $field_required && !str_contains($field_label, '*') ? $field_label . '<span class="required">*</span>' : $field_label;
$field_description = isset($field['description']) ? $field['description'] : null;
$field_explanation = isset($field['explanation']) ? $field['explanation'] : null;
$placeholder = isset($field['placeholder']) ? $field['placeholder'] : null;
$field_accept = isset($field['accept']) ? $field['accept'] : null;
$field_cta_text = isset($field['cta_text']) ? $field['cta_text'] : null;
$field_min_value = isset($field['min']) ? $field['min'] : null;
$field_min_date_value = isset($field['min_date']) ? $field['min_date'] : null;
$field_max_value = isset($field['max']) ? $field['max'] : null;
$field_col_class = isset($field['class']) ? $field['class'] : 'col-auto';
$field_fields = isset($field['fields']) ? $field['fields'] : null;
$field_date = isset($field['date']) ? $field['date'] : false;
$field_time = isset($field['time']) ? $field['time'] : null; // time picker
$field_pattern = isset($field['pattern']) ? $field['pattern'] : null; //regex pattern
$field_maxlength = isset($field['maxlength']) ? $field['maxlength'] : null;
$field_input_class = isset($field['input_class']) && !empty($field['input_class']) ? explode(' ', $field['input_class']) : [];
$field_icon = isset($field['icon']) ? $field['icon'] : null;
$field_price = isset($field['price']) ? $field['price'] : null;
$field_group = isset($field['group']) ? $field['group'] : null; // inputs can be grouped together
$field_autocomplete = isset($field['autocomplete']) && $field['autocomplete'] === 'true' ? 'on' : 'off';
$conditions = isset($field['conditions']) && !empty($field['conditions']) ? json_encode($field['conditions']) : '';

if (!in_array($field_type, Growtype_Form_General::GROWTYPE_FORM_ALLOWED_FIELD_TYPES)) {
    return null;
}

/**
 * Extra attributes
 */
if ($field_date || $field_time) {
    $field_pattern = '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])[ ](0[0-9]|1[0-9]|2[0-4]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])';
    $field_maxlength = '16';
}

/**
 * Extra classes
 */
if ($field_date && $field_time) {
    array_push($field_input_class, 'datetimepicker');
} elseif ($field_date) {
    array_push($field_input_class, 'datepicker');
} elseif ($field_time) {
    array_push($field_input_class, 'timepicker');
}

if (!empty($field_value)) {
    array_push($field_input_class, 'has-value');
}

if (!empty($field_price)) {
    array_push($field_input_class, 'autonumeric');
}

/**
 * Input class convert to string
 */
$field_input_class = implode(" ", $field_input_class);

/**
 * Block cat types
 */
$block_cat_types = ['repeater', 'custom', 'shortcode', 'checkbox'];
?>

<div class="<?= in_array($field_type, $block_cat_types) ? 'b-wrapper' : 'e-wrapper'; ?> <?= $field_col_class ?>"
     style="<?= $field_hidden ? 'display:none;' : '' ?>"
     data-name="<?= $field_name ?>"
     data-label="<?= !empty($field_label) ? 'true' : 'false' ?>"
     data-group="<?= $field_group ?>"
     data-conditions='<?= $conditions ?>'
>
    <?php if (!empty($field_icon)) { ?>
        <div class="input-icon">
            <?= $field_icon ?>
        </div>
    <?php } ?>

    <?php if (!in_array($field_type, ['checkbox']) && !empty($field_label)) { ?>
        <label for="<?= $field_name ?>" class="form-label">
            <?= $field_label ?>
        </label>
    <?php } ?>

    <?php if (!empty($field_description) && $field_type !== 'custom') { ?>
        <p class="field-description"><?= $field_description ?></p>
    <?php } ?>

    <?php if ($field_type === 'select') {
        include 'partials/select.php';
    } elseif ($field_type === 'radio') {
        include 'partials/radio.php';
    } elseif ($field_type === 'checkbox') {
        include 'partials/checkbox.php';
    } elseif ($field_type === 'textarea') {
        include 'partials/textarea.php';
    } elseif ($field_type === 'file') {
        include 'partials/file.php';
    } elseif ($field_type === 'custom') {
        include 'partials/custom.php';
    } elseif ($field_type === 'shortcode') {
        include 'partials/shortcode.php';
    } elseif ($field_type === 'repeater') {
        include 'partials/repeater.php';
    } else {
        include 'partials/general.php';
    } ?>

    <?php if (!empty($field_explanation)) { ?>
        <p class="field-explanation"><?= $field_explanation ?></p>
    <?php } ?>
</div>
