<textarea id="<?= $field_name ?>"
          name="<?= $field_name ?>"
          class="form-control <?= $field_input_class ?>"
          rows="<?= $textarea_rows ?>"
          <?= isset($field_min_value) && !$field_date ? 'minlength="' . $field_min_value . '"' : '' ?>
          cols="<?= $textarea_cols ?>"
          placeholder="<?= $placeholder ?>" <?= $field_required ? 'required' : '' ?>><?= $field_value ?></textarea>
