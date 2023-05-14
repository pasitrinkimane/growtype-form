<?php if (isset($field_options) && !empty($field_options)) { ?>
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
