<?php
if (isset($_REQUEST['repeater']) && !empty($_REQUEST['repeater'])) {
    $index = 1;
    $amount = count($_REQUEST['repeater']);
    foreach ($_REQUEST['repeater'] as $key => $repeater) {
        ?>
        <div class="row g-3 repeater-fields" data-form-nr="<?= $index ?>">
            <?php
            if (isset($field['remove_text'])) { ?>
                <a href="javascript:void(0);" class="btn btn-link btn-remove" style="<?= $index === 1 ? 'display: none;' : '' ?>"><?= $field['remove_text'] ?></a>
            <?php } ?>

            <?php

            foreach ($field_fields as $single_field) {
                $field_name = $single_field['name'] ?? null;
                if (str_contains($field_name, '[')) {
                    $cat_p = explode('[', $field_name)[0] ?? null;
                    $cat_c = explode('[', $field_name)[1] ?? null;
                    $cat_c = str_replace(']', '', $cat_c);

                    $field_name = str_replace($cat_p, $key, $field_name);

                    $single_field['name'] = $field_name;
                }

                if (isset($single_field['type']) && isset($single_field['value']) && str_contains($single_field['value'], '1')) {
                    $single_field['value'] = str_replace('1', $index, $single_field['value']);
                }
                if (isset($single_field['name'])) {
                    $name = explode('[', $single_field['name'])[1];
                    $name = str_replace(']', '', $name);
                    $single_field['value'] = $repeater[$name] ?? null;
                }

                Growtype_Form_Render::render_growtype_form_field($single_field);
            }
            ?>

            <?php
            if (isset($field['duplicate_text'])) { ?>
                <div class="col-12 mt-4 pt-2 btn-wrapper" style="<?= $index !== $amount ? 'display: none;' : '' ?>">
                    <a href="javascript:void(0);" class="btn btn-secondary btn-add"><?= $field['duplicate_text'] ?></a>
                </div>
            <?php } ?>
        </div>
        <?php
        $index++;
    }
} else { ?>
    <div class="row g-3 repeater-fields" data-form-nr="1">
        <?php
        if (isset($field['remove_text'])) { ?>
            <a href="javascript:void(0);" class="btn btn-link btn-remove" style="display: none;"><?= $field['remove_text'] ?></a>
        <?php } ?>

        <?php
        foreach ($field_fields as $single_field) {
            Growtype_Form_Render::render_growtype_form_field($single_field);
        }
        ?>

        <?php
        if (isset($field['duplicate_text'])) { ?>
            <div class="col-12 mt-4 pt-2 btn-wrapper">
                <a href="javascript:void(0);" class="btn btn-secondary btn-add"><?= $field['duplicate_text'] ?></a>
            </div>
        <?php } ?>
    </div>
<?php } ?>
