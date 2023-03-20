<div class="input-file-wrapper">
    <?php if (!empty($field_value) && isset($field_value['url'])) { ?>
        <div class="b-file-preview">
            <a href="<?= $field_value['url'] ?>" target="_blank">
                <?php if (@is_array(getimagesize($field_value['url']))) {
                    echo '<img class="img-fluid" src="' . $field_value['url'] . '" alt="">';
                } else {
                    echo '<span class="dashicons dashicons-media-document"></span>';
                }
                ?>
            </a>
            <span class="btn-img-remove dashicons dashicons-remove" data-type="<?= $field_type ?>" data-id="<?= $field_name ?>" data-class="<?= $field_input_class ?>" data-name="<?= $field_name ?>" data-accept="<?= $field_accept ?>" data-required="<?= $field_required ?>" style="cursor: pointer;"></span>
            <?php
            if (isset($field_value['name'])) { ?>
                <a href="<?= $field_value['url'] ?>" target="_blank" class="e-title"><?= $field_value['name'] ?></a>
            <?php } ?>
        </div>
    <?php } else { ?>
        <input
            type="<?= $field_type ?>"
            id="<?= $field_name ?>"
            class="form-control <?= $field_input_class ?>"
            data-placeholder="<?= $placeholder ?>"
            data-selected-placeholder-single="<?= isset($field['selected_placeholder_single']) ? $field['selected_placeholder_single'] : '' ?>"
            data-selected-placeholder-multiple="<?= isset($field['selected_placeholder_multiple']) ? $field['selected_placeholder_multiple'] : '' ?>"
            <?= !empty($field_cta_text) ? 'data-text="' . $field_cta_text . '"' : '' ?>
            data-buttonBefore="true"
            name="<?= $field_name ?>"
            accept="<?= $field_accept ?>"
            <?= $field_required ? 'required' : '' ?>
            <?= $field_multiple ? 'multiple' : '' ?>
            max-size="<?= isset($field['max_size']) ? $field['max_size'] : '3000000' ?>"
            max-size-error-message="<?= isset($field['max_size_error_message']) ? $field['max_size_error_message'] : '' ?>"
        >
        <?php if (!empty($placeholder)) { ?>
            <div class="form-label-placeholder">
                <?php echo $placeholder ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>
