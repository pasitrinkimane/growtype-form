<input type="<?= $field_type ?>"
       class="form-control <?= $field_input_class ?> domain"
       name="<?= $field_name ?>"
       id="<?= $field_name ?>"
       placeholder="<?= $placeholder ?? null ?>"
    <?= $field_required ? 'required' : '' ?>
    <?= isset($field_maxlength) ? 'maxlength="' . $field_maxlength . '"' : '' ?>
    <?= isset($field_pattern) ? 'pattern="' . $field_pattern . '"' : '' ?>
       value="<?= $field_value ?>"
       autocomplete="<?= $field_autocomplete ?>"
    <?= isset($field_min_value) ? 'min="' . $field_min_value . '"' : '' ?>
    <?= isset($field_max_value) ? 'max="' . $field_max_value . '"' : '' ?>
>
