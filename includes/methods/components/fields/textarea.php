<?php if (!empty($field_label)) { ?>
    <label for="<?= $field_name ?>" class="form-label">
        <?= $field_label ?>
    </label>
<?php } ?>

<?php if (!empty($field_description)) { ?>
    <p class="form-description"><?= $field_description ?></p>
<?php } ?>

<textarea id="<?= $field_name ?>" name="<?= $field_name ?>" rows="4" cols="50" placeholder="<?= $placeholder ?>" <?= $field_required ? 'required' : '' ?>><?= $field_value ?></textarea>

