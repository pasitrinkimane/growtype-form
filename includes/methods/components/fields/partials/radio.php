<div class="radio-inputs-wrapper">
    <?php foreach ($field_options as $index => $field_option) { ?>
        <div class="radio-wrapper" data-name="<?= $field_option['value'] ?>">
            <input type="radio" id="<?= $field_option['value'] ?>" <?= isset($field_option['checked']) && $field_option['checked'] ? 'checked' : ($index === 0 ? 'checked' : '') ?> name="<?= $field_name ?>" value="<?= $field_option['value'] ?>" <?= $field_required ? 'required' : '' ?>>
            <label for="<?= $field_option['value'] ?>"><?= $field_option['label'] ?></label>
        </div>
    <?php } ?>
</div>
