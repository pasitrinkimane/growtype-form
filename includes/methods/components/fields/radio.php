<?php foreach ($field_options as $field_option) { ?>
    <div class="radio-wrapper">
        <input type="radio" id="<?= str_replace(' ', '_', strtolower($field_option)) ?>" name="<?= $field_name ?>" value="<?= strtolower($field_option) ?>" <?= $field_required ? 'required' : '' ?>>
        <label for="<?= str_replace(' ', '_', strtolower($field_option)) ?>"><?= str_replace('_', ' ', $field_option) ?></label>
    </div>
<?php }
