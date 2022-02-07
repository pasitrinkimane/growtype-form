<?php
$field_name = $field['name'] ?? false;
$field_required = isset($field['required']) && $field['required'] === 'true' ? true : false;
$field_type = $field['type'];
$field_hidden = $field['hidden'] ?? false;

if ($field_type === 'hidden') {
    $field_hidden = true;
}

$field_value = isset($field['value']) ? sanitize_text_field($field['value']) : null;

if (empty($field_value)) {
    $field_value = isset($_REQUEST[$field_name]) ? sanitize_text_field($_REQUEST[$field_name]) : null;
}

if ($field_name === 'name') {
    $field_value = $_REQUEST[self::ALTERNATIVE_SUBMITTED_DATA_KEYS[$field_name]] ?? null;
}

if (str_contains($field_name, 'password')) {
    $field_value = null;
}

$field_options = $field['options'] ?? null;
$field_label = $field['label'] ?? null;
$field_label = $field_required && !str_contains($field_label, '*') ? $field_label . '<span class="required">*</span>' : $field_label;
$field_description = $field['description'] ?? null;
$placeholder = $field['placeholder'] ?? null;
$field_accept = $field['accept'] ?? null;
$field_min_value = $field['min'] ?? null;
$field_max_value = $field['max'] ?? null;
$field_col_class = $field['col_class'] ?? 'col-auto';

if (!in_array($field_type, self::GROWTYPE_FORM_ALLOWED_FIELD_TYPES)) {
    return null;
}
?>

<div class="<?= $field_col_class ?>" style="<?= $field_hidden ? 'display:none;' : '' ?>" data-name="<?= $field_name ?>">
    <?php
    /**
     * Select
     */
    if ($field_type === 'select') {
    if (!empty($field_label)) { ?>
        <label for="<?= $field_name ?>" class="form-label">
            <?= $field_label ?>
        </label>
    <?php } ?>
        <select name="<?= $field_name ?>" id="<?= $field_name ?>" <?= $field_required ? 'required' : '' ?>>
            <?php
            /**
             * Use woocommerce country select
             */
            if (class_exists('woocommerce') && $field_value === 'wc_country' && $field_type === 'select') {
                $field_options = array ('' => __('Select a country / region&hellip;', 'woocommerce')) + WC()->countries->get_allowed_countries();

                foreach ($field_options as $key => $field_option) { ?>
                    <option value="<?= $key ?>" <?= isset($_REQUEST[$field_name]) && $_REQUEST[$field_name] === $key ? 'selected' : '' ?>><?= $field_option ?></option>
                <?php }
            } else {
                foreach ($field_options as $field_option) { ?>
                    <option value="<?= sanitize_text_field($field_option['value']) ?>" <?= isset($_REQUEST[$field_name]) && $_REQUEST[$field_name] === $field_option['value'] ? 'selected' : '' ?>><?= sanitize_text_field($field_option['label']) ?></option>
                <?php }
            } ?>
        </select>
    <?php
    /**
     * Radio
     */
    } elseif ($field_type === 'radio') {
    foreach ($field_options

    as $field_option) { ?>
        <div class="radio-wrapper">
            <input type="radio" id="<?= str_replace(' ', '_', strtolower($field_option)) ?>" name="<?= $field_name ?>" value="<?= strtolower($field_option) ?>" <?= $field_required ? 'required' : '' ?>>
            <label for="<?= str_replace(' ', '_', strtolower($field_option)) ?>"><?= str_replace('_', ' ', $field_option) ?></label>
        </div>
    <?php }
    /**
     * Checkbox
     */
    } elseif ($field_type === 'checkbox') { ?>
        <div class="form-check">
            <input type="<?= $field_type ?>"
                   class="form-check-input"
                   name="<?= $field_name ?>"
                   id="<?= $field_name ?>"
                   placeholder="<?= $placeholder ?>"
                <?= $field_required ? 'required' : '' ?>
                   value="<?= $field_value ?>"
            >
            <?php
            if (!empty($field_label)) { ?>
                <label for="<?= $field_name ?>" class="form-label">
                    <?= $field_label ?>
                </label>
            <?php }
            ?>
        </div>
    <?php
    /**
     * Textarea
     */
    } elseif ($field_type === 'textarea') { ?>
    <?php
    if (!empty($field_label)) { ?>
        <label for="<?= $field_name ?>" class="form-label">
            <?= $field_label ?>
        </label>
    <?php }
        ?>
        <?php if (!empty($field_description)) { ?>
        <p class="form-description"><?= $field_description ?></p>
    <?php } ?>
        <textarea id="<?= $field_name ?>" name="<?= $field_name ?>" rows="4" cols="50" placeholder="<?= $placeholder ?>" <?= $field_required ? 'required' : '' ?>><?= $field_value ?></textarea>
    <?php
    /**
     * File
     */
    } elseif ($field_type === 'file') { ?>
    <?php if (!empty($field_label)) { ?>
        <label for="<?= $field_name ?>" class="form-label">
            <?= $field_label ?>
        </label>
    <?php } ?>
        <div class="img-wrapper">
            <?php
            if (!empty($field_value)) { ?>
                <img class="img-fluid" src="<?= $field_value ?>" alt="" style="max-width: 150px;">
                <span class="btn-img-remove dashicons dashicons-remove" data-type="<?= $field_type ?>" data-id="<?= $field_name ?>" data-name="<?= $field_name ?>" data-accept="<?= $field_accept ?>" data-required="<?= $field_required ?>" style="cursor: pointer;"></span>
            <?php } else { ?>
                <input type="<?= $field_type ?>" id="<?= $field_name ?>" name="<?= $field_name ?>" accept="<?= $field_accept ?>" <?= $field_required ? 'required' : '' ?>>
            <?php } ?>
        </div>
        <script>
            $=jQuery;
            $('.btn-img-remove').click(function () {
                let type = $(this).attr('data-type');
                let id = $(this).attr('data-id');
                let name = $(this).attr('data-name');
                let accept = $(this).attr('data-accept');
                let required = $(this).attr('data-required');
                $(this).closest('.img-wrapper').hide();
                $(this).closest('.col-auto').append('<input type="' + type + '" id="' + id + '" name="' + name + '"  accept="' + accept + '"  ' + required + '>');
            });
        </script>
    <?php
    /**
     * Custom, skip sanitization
     */
    } elseif ($field_type === 'custom') {
        echo $field['value'];
        /**
         * Input
         */
    }
    else { ?>
    <?php
    if (!empty($field_label)) { ?>
        <label for="<?= $field_name ?>" class="form-label">
            <?= $field_label ?>
        </label>
    <?php }
        ?>
        <?php if (!empty($field_description)) { ?>
        <p class="form-description"><?= $field_description ?></p>
    <?php } ?>
    <input type="<?= $field_type ?>"
           class="form-control <?= !empty($field_value) ? 'has-value' : '' ?>"
           name="<?= $field_name ?>"
           id="<?= $field_name ?>"
           placeholder="<?= $placeholder ?? null ?>"
        <?= $field_required ? 'required' : '' ?>
           value="<?= $field_value ?>"
        <?= $field_min_value ? 'min="' . $field_min_value . '"' : '' ?>
        <?= $field_max_value ? 'max="' . $field_max_value . '"' : '' ?>
    >
    <?php } ?>
</div>
