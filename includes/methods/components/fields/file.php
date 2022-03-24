<?php if (!empty($field_label)) { ?>
    <label for="<?= $field_name ?>" class="form-label">
        <?= $field_label ?>
    </label>
<?php } ?>

<div class="input-file-wrapper">
    <?php if (!empty($field_value)) { ?>
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
        <input type="<?= $field_type ?>" id="<?= $field_name ?>" class="<?= $field_input_class ?>" data-placeholder="<?= $placeholder ?>" <?= !empty($field_cta_text) ? 'data-text="' . $field_cta_text . '"' : '' ?> data-buttonBefore="true" name="<?= $field_name ?>" accept="<?= $field_accept ?>" <?= $field_required ? 'required' : '' ?>>
    <?php } ?>
</div>
