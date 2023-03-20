<?php
$field_name = $field['name'] ?? false;
$field_required = isset($field['required']) && $field['required'] === 'true' ? true : false;
$field_multiple = isset($field['multiple']) && $field['multiple'] === 'true' ? true : false;
$field_type = $field['type'];
$field_hidden = $field['hidden'] ?? false;

if ($field_type === 'hidden') {
    $field_hidden = true;
}

$field_value = isset($field['value']) ? sanitize_text_field($field['value']) : null;

if (empty($field_value)) {
    $field_value = isset($_REQUEST[$field_name]) && $field_type !== 'file' ? sanitize_text_field($_REQUEST[$field_name]) : $_REQUEST[$field_name] ?? null;
}

if ($field_name === 'name') {
    $field_value = $_REQUEST[Growtype_Form_Crud::ALTERNATIVE_SUBMITTED_DATA_KEYS[$field_name]] ?? null;
}

if (str_contains($field_name, 'password')) {
    $field_value = null;
}

$field_options = $field['options'] ?? null;

/**
 * Advanced select options
 */
if (!empty($field_options) && !is_array($field_options) && (str_contains($field_options, 'taxonomy=') || str_contains($field_options, 'product_cat='))) {
    $select_type = 'custom';
    $field_values = explode('&', $field_options);

    $term_slug = 'alcohol-type';
    $taxonomy_name = 'product_cat';

    foreach ($field_values as $field_value) {
        if (str_contains($field_value, 'taxonomy=')) {
            $taxonomy_name = str_replace('taxonomy=', '', $field_value);
        }
        if (str_contains($field_value, 'product_cat=')) {
            $term_slug = str_replace('product_cat=', '', $field_value);
        }
    }

    $term = get_term_by('slug', $term_slug, $taxonomy_name);
    $term_children = get_term_children($term->term_id, $taxonomy_name);

    $field_options = array ('' => __('Select a value&hellip;', 'woocommerce')) + [];
    foreach ($term_children as $child) {
        $term = get_term_by('id', $child, $taxonomy_name);
        $field_options[$term->slug] = $term->name;
    }
} elseif ($field_options === 'wc_countries') {
    $select_type = 'custom';
    $field_options = array ('' => __('Select a country / region&hellip;', 'woocommerce')) + WC()->countries->get_allowed_countries();
}

$field_label = isset($field['label']) ? $field['label'] : null;
$field_label = !empty($field_label) && $field_required && !str_contains($field_label, '*') ? $field_label . '<span class="required">*</span>' : $field_label;
$field_description = isset($field['description']) ? $field['description'] : null;
$placeholder = isset($field['placeholder']) ? $field['placeholder'] : null;
$field_accept = isset($field['accept']) ? $field['accept'] : null;
$field_cta_text = isset($field['cta_text']) ? $field['cta_text'] : null;
$field_min_value = isset($field['min']) ? $field['min'] : null;
$field_max_value = isset($field['max']) ? $field['max'] : null;
$field_col_class = isset($field['class']) ? $field['class'] : 'col-auto';
$field_fields = isset($field['fields']) ? $field['fields'] : null;
$field_date = isset($field['date']) ? $field['date'] : null;
$field_time = isset($field['time']) ? $field['time'] : null; // time picker
$field_pattern = isset($field['pattern']) ? $field['pattern'] : null; //regex pattern
$field_maxlength = isset($field['maxlength']) ? $field['maxlength'] : null;
$field_input_class = isset($field['input_class']) && !empty($field['input_class']) ? explode(' ', $field['input_class']) : [];
$field_icon = isset($field['icon']) ? $field['icon'] : null;
$field_price = isset($field['price']) ? $field['price'] : null;
$field_group = isset($field['group']) ? $field['group'] : null; // inputs can be grouped together
$field_autocomplete = isset($field['autocomplete']) && $field['autocomplete'] === 'true' ? 'on' : 'off';
$conditions = isset($field['conditions']) ? json_encode($field['conditions']) : null;

if (!in_array($field_type, Growtype_Form_Render::GROWTYPE_FORM_ALLOWED_FIELD_TYPES)) {
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
     style="<?= $field_hidden || !empty($conditions) ? 'display:none;' : '' ?>"
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

    <?php if (!empty($field_label)) { ?>
        <label for="<?= $field_name ?>" class="form-label">
            <?= $field_label ?>
        </label>
    <?php } ?>

    <?php if (!empty($field_description)) { ?>
        <p class="form-description"><?= $field_description ?></p>
    <?php } ?>

    <?php
    if ($field_type === 'select') {
        include 'fields/select.php';
    } elseif ($field_type === 'radio') {
        include 'fields/radio.php';
    } elseif ($field_type === 'checkbox') {
        include 'fields/checkbox.php';
    } elseif ($field_type === 'textarea') {
        include 'fields/textarea.php';
    } elseif ($field_type === 'file') {
        include 'fields/file.php';
    } elseif ($field_type === 'custom') {
        include 'fields/custom.php';
    } elseif ($field_type === 'shortcode') {
        include 'fields/shortcode.php';
    } elseif ($field_type === 'repeater') {
        include 'fields/repeater.php';
    } else {
        include 'fields/general.php';
    } ?>
</div>
