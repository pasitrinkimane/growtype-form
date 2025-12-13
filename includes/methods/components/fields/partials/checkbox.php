<?php if (isset($field_options) && !empty($field_options)) { ?>
    <div class="form-check-wrapper" aria-required="true">
        <?php foreach ($field_options as $field_option) { ?>
            <div class="form-check">
                <input type="<?php echo $field_type ?>"
                       class="form-check-input"
                       name="<?php echo $field_option['name'] ?>"
                       value="<?php echo $field_option['value'] ?>"
                       id="checkbox-<?php echo trim($field_option['value']) ?>"
                    <?php echo in_array($field_option['value'], $selected_options) ? 'checked' : '' ?>
                >
                <?php
                if (isset($field_option['label']) && !empty($field_option['label'])) { ?>
                    <label for="checkbox-<?php echo trim($field_option['value']) ?>" class="form-label">
                        <?php echo $field_option['label'] ?>
                    </label>
                <?php }
                ?>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <div class="form-check">
        <input type="<?php echo $field_type ?>"
               class="form-check-input"
               name="<?php echo $field_name ?>"
               id="<?php echo $field_id ?>"
               placeholder="<?php echo $placeholder ?>"
            <?php echo $field_required ? 'required' : '' ?>
               value="<?php echo $field_value ?>"
            <?php echo (in_array('true', $selected_options) || in_array('1', $selected_options)) ? 'checked' : '' ?>
        >
        <?php if (!empty($field_label)) { ?>
            <label for="<?php echo $field_id ?>" class="form-label">
                <?php echo $field_label ?>
            </label>
        <?php } ?>
    </div>
<?php }
