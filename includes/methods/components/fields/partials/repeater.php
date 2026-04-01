<?php
$repeater_values = [];
$field_key = $field_name ?? 'repeater';

if (!empty($field_value) && is_array($field_value)) {
    $repeater_values = $field_value;
} else {
    $request_values = [];
    if (isset($_REQUEST[$field_key]) && is_array($_REQUEST[$field_key])) {
        $request_values[$field_key] = $_REQUEST[$field_key];
    }
    foreach ($_REQUEST as $key => $value) {
        if (strpos($key, $field_key . '_') === 0 && is_array($value)) {
            $request_values[$key] = $value;
        }
    }
    if (!empty($request_values)) {
        $ordered = [];
        if (isset($request_values[$field_key])) {
            $ordered[] = $request_values[$field_key];
            unset($request_values[$field_key]);
        }
        $suffixes = [];
        foreach ($request_values as $key => $value) {
            if (preg_match('/^' . preg_quote($field_key, '/') . '_(\d+)$/', $key, $matches)) {
                $suffixes[(int)$matches[1]] = $value;
            } else {
                $suffixes[] = $value;
            }
        }
        ksort($suffixes);
        foreach ($suffixes as $value) {
            $ordered[] = $value;
        }
        $repeater_values = $ordered;
    }
}

$build_field_name = function ($name, $index) {
    if ($index <= 1) {
        return $name;
    }
    if (strpos($name, '[') !== false) {
        $parts = explode('[', $name, 2);
        return $parts[0] . '_' . $index . '[' . $parts[1];
    }
    return $name . '_' . $index;
};

if (!empty($repeater_values)) {
    $index = 1;
    $amount = count($repeater_values);
    foreach ($repeater_values as $repeater) {
        ?>
        <div class="repeater-fields" data-form-nr="<?= $index ?>">
            <?php
            if (isset($field['remove_text'])) { ?>
                <a href="javascript:void(0);" class="btn btn-link btn-remove" style="<?= $index === 1 ? 'display: none;' : '' ?>"><?= $field['remove_text'] ?></a>
            <?php } ?>

            <?php
            foreach ($field_fields as $single_field) {
                $base_field_name = $single_field['name'] ?? null;
                if (!empty($base_field_name)) {
                    $single_field['name'] = $build_field_name($base_field_name, $index);
                }

                if (isset($single_field['type']) && isset($single_field['value']) && strpos($single_field['value'], '1') !== false) {
                    $single_field['value'] = str_replace('1', $index, $single_field['value']);
                }

                if (!empty($base_field_name)) {
                    $value_key = null;
                    if (preg_match('/\[(.*?)\]/', $base_field_name, $matches)) {
                        $value_key = $matches[1];
                    }
                    if (is_array($repeater)) {
                        if (!empty($value_key)) {
                            $single_field['value'] = $repeater[$value_key] ?? null;
                        } else {
                            $single_field['value'] = $repeater;
                        }
                    } else {
                        $single_field['value'] = $repeater;
                    }
                }

                Growtype_Form_General::render_growtype_form_field($single_field);
            }
            ?>

            <?php
            if (isset($field['duplicate_text'])) { ?>
                <div class="col-12 mt-1 pt-2 btn-wrapper" style="<?= $index !== $amount ? 'display: none;' : '' ?>">
                    <a href="javascript:void(0);" class="btn btn-secondary btn-add"><?= $field['duplicate_text'] ?></a>
                </div>
            <?php } ?>
        </div>
        <?php
        $index++;
    }
} else { ?>
    <div class="repeater-fields" data-form-nr="1">
        <?php
        if (isset($field['remove_text'])) { ?>
            <a href="javascript:void(0);" class="btn btn-link btn-remove" style="display: none;"><?= $field['remove_text'] ?></a>
        <?php } ?>

        <?php
        foreach ($field_fields as $single_field) {
            Growtype_Form_General::render_growtype_form_field($single_field);
        }
        ?>

        <?php
        if (isset($field['duplicate_text'])) { ?>
            <div class="col-12 mt-1 pt-2 btn-wrapper">
                <a href="javascript:void(0);" class="btn btn-secondary btn-add"><?= $field['duplicate_text'] ?></a>
            </div>
        <?php } ?>
    </div>
<?php } ?>
