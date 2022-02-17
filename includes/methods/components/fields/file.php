<?php if (!empty($field_label)) { ?>
    <label for="<?= $field_name ?>" class="form-label">
        <?= $field_label ?>
    </label>
<?php } ?>

<div class="input-file-wrapper">
    <?php if (!empty($field_value)) { ?>
        <a href="<?= $field_value ?>" target="_blank">
            <?php if (@is_array(getimagesize($field_value))) {
                echo '<img class="img-fluid" src="' . $field_value . '" alt="">';
            } else {
                echo '<span class="dashicons dashicons-media-document"></span>';
            }
            ?>
        </a>
        <span class="btn-img-remove dashicons dashicons-remove" data-type="<?= $field_type ?>" data-id="<?= $field_name ?>" data-name="<?= $field_name ?>" data-accept="<?= $field_accept ?>" data-required="<?= $field_required ?>" style="cursor: pointer;"></span>
    <?php } else { ?>
        <input type="<?= $field_type ?>" id="<?= $field_name ?>" name="<?= $field_name ?>" accept="<?= $field_accept ?>" <?= $field_required ? 'required' : '' ?>>
    <?php } ?>
</div>
