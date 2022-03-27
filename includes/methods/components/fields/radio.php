<?php foreach ($field_options as $index => $field_option) { ?>
    <div class="radio-wrapper">
        <input type="radio" id="<?= $field_option['id'] ?>" <?= isset($field_option['checked']) && $field_option['checked'] ? 'checked' : ($index === 0 ? 'checked' : '') ?> name="<?= $field_name ?>" value="<?= $field_option['value'] ?>" <?= $field_required ? 'required' : '' ?>>
        <label for="<?= $field_option['id'] ?>"><?= $field_option['label'] ?></label>
    </div>
<?php }
