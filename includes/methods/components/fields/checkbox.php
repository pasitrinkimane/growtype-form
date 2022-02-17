<?php
$selected_options = [];

if (isset($_REQUEST[$field_name])) {
    $selected_options = is_array($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : explode(',', $_REQUEST[$field_name]);
} elseif (str_contains($field_name, '[')) {
    $cat_p = explode('[', $field_name)[0] ?? null;
    $cat_c = explode('[', $field_name)[1] ?? null;
    $cat_c = str_replace(']', '', $cat_c);
    $selected_options = $_REQUEST[$cat_p][$cat_c] ?? [];
}

?>

<?php if (isset($field_options)) { ?>
    <label class="form-label">
        <?= $field_label ?>
    </label>
    <div class="form-check-wrapper" aria-required="true">
        <?php
        foreach ($field_options as $field_option) { ?>
            <div class="form-check">
                <input type="<?= $field_type ?>"
                       class="form-check-input"
                       name="<?= $field_option['name'] ?>"
                       value="<?= $field_option['value'] ?>"
                       id="<?= $field_option['name'] ?>"
                    <?= in_array($field_option['value'], $selected_options) ? 'checked' : '' ?>
                >
                <?php
                if (isset($field_option['label']) && !empty($field_option['label'])) { ?>
                    <label for="<?= $field_option['name'] ?>" class="form-label">
                        <?= $field_option['label'] ?>
                    </label>
                <?php }
                ?>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
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
<?php }
