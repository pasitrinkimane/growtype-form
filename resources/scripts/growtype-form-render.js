$ = jQuery;

/**
 * @param $duplicateBtn
 */
function cloneReapeaterForm($duplicateBtn) {
    $duplicateBtn.click(function (e) {
        e.preventDefault();

        let initialRepeaterForm = $('.repeater-fields[data-form-nr="1"]');
        let repeaterForm = $(this).closest('.repeater-fields');
        let repeaterFormClone = initialRepeaterForm.clone();
        let formNr = repeaterForm.attr('data-form-nr');
        let newFormNr = parseInt(formNr) + 1;

        if (repeaterForm.closest('.b-wrapper').hasClass('repeater-fields-folded')) {
            /**
             * Remove closest form group validation
             */
            let blockName = repeaterForm.closest('.b-wrapper').attr('data-group');
            $('.b-wrapper[data-group="' + blockName + '"]')
                .find('.form-check-wrapper[aria-required="true"]')
                .attr('aria-required', 'false')
                .find('input').prop('checked', false);

            repeaterForm.closest('.b-wrapper').removeClass('repeater-fields-folded');
            repeaterForm.find('.btn-remove').show();
            repeaterForm.find('.btn-add').hide();
            return false;
        }

        /**
         * Hide duplicate btn in previous form
         */
        $(this).closest('.btn-wrapper').hide();

        repeaterFormClone.hide();
        repeaterFormClone.insertAfter(repeaterForm)
        repeaterFormClone.find('.btn-remove').show();
        repeaterFormClone.find('.btn-wrapper').show();
        repeaterFormClone.find('.e-counter').text(newFormNr);
        repeaterFormClone.attr('data-form-nr', newFormNr);

        /**
         * Reset cloned form
         */
        repeaterFormClone.find('.chosen-container').remove();
        repeaterFormClone.find('label.error').remove();
        repeaterFormClone.find('.error').removeClass('error');
        repeaterFormClone.find('input').val('');
        repeaterFormClone.find('select').val('');

        /**
         * Rename cloned form
         */
        renameClonedForm(initialRepeaterForm, repeaterFormClone, newFormNr);

        /**
         *
         */
        repeaterFormClone.find('select').chosen(window.selectArgs);

        /**
         * Show cloned form
         */
        repeaterFormClone.fadeIn();

        /**
         * Reinitiate method
         */
        cloneReapeaterForm(repeaterFormClone.find('a.btn-add'));
        removeRepeaterForm(repeaterFormClone.find('.btn-remove'));
    });
}

/**
 *
 * @param repeaterFormClone
 * @param newFormNr
 */
function renameClonedForm(initialRepeaterForm, repeaterFormClone, newFormNr) {
    initialRepeaterForm.find('.e-wrapper').map(function (index, element) {
        let groupName = $(element).attr('data-name');
        if (typeof groupName !== 'undefined' && groupName.length > 0) {
            let newName = '';
            if (groupName.match(/\[/g) !== null) {
                groupName = groupName.split("[")
                newName = groupName[0] + '_' + newFormNr + '[' + groupName[1];
            } else {
                newName = groupName + '_' + newFormNr;
            }

            let field = $(repeaterFormClone.find('div')[index + 1]);
            field.attr('data-name', newName);
            field.find('label').attr('for', newName)
            field.find('.form-control')
                .attr('name', newName)
                .attr('id', newName)
            field.find('select')
                .attr('name', newName)
                .attr('id', newName)
        }
    });
}

/**
 *
 */
function removeRepeaterForm(removeBtn) {
    removeBtn.click(function () {
        let repeaterForm = $(this).closest('.repeater-fields');

        if (repeaterForm.attr('data-form-nr') === '1') {
            /**
             * Add closest form group validation
             */
            let blockName = repeaterForm.closest('.b-wrapper').attr('data-group');
            $('.b-wrapper[data-group="' + blockName + '"]').find('.form-check-wrapper[aria-required="false"]').attr('aria-required', 'true');

            repeaterForm.closest('.b-wrapper').addClass('repeater-fields-folded');
            repeaterForm.find('.btn-add').fadeIn();
            return false;
        }

        if (repeaterForm.find('.btn-wrapper:visible').length > 0) {
            repeaterForm.fadeOut().promise().done(function () {
                repeaterForm.prev('.repeater-fields').find('.btn-wrapper').fadeIn();
                $(this).remove();
            })
        } else {
            repeaterForm.fadeOut().promise().done(function () {
                $(this).remove();
                $('.repeater-fields').not('[data-form-nr="1"]').map(function (index, element) {
                    let formNr = index + 2;
                    $(element).attr('data-form-nr', formNr)
                        .find('.e-counter')
                        .hide()
                        .text(formNr)
                        .fadeIn();
                    renameClonedForm($('.repeater-fields[data-form-nr="1"]'), $(element), formNr);
                });
            });
        }
    });
}

/**
 *
 */
$('document').ready(function () {
    cloneReapeaterForm($('.repeater-fields a.btn-add'));
    removeRepeaterForm($('.btn-remove'));

    /**
     * Image uploader setup
     */
    $('.image-uploader').each(function () {
        let image_upload_data = typeof (growtype_form_image_upload_data) !== 'undefined' ? growtype_form_image_upload_data : [];
        let preloaded = [];

        if (Object.entries(image_upload_data).length > 0) {
            let preloaded_data = JSON.parse(image_upload_data.preloaded);

            if (Object.entries(preloaded_data).length > 0) {
                preloaded = preloaded_data;
            }
        }

        $(this).imageUploader({
            preloaded: preloaded,
            imagesInputName: $(this).attr('data-name'),
            extensions: $(this).attr('data-extensions').split(","),
            maxSize: $(this).attr('data-max-size'),
        });
    });

    /**
     * Set date
     */
    if ($(".datepicker").length > 0) {
        $(".datepicker").datepicker();
    }

    /**
     * Set time
     */
    if ($(".timepicker").length > 0) {
        $('.timepicker').timepicker();
    }

    /**
     * Set date and time pickers
     */
    if ($(".datetimepicker").length > 0) {
        const date = new Date();
        let hour = date.getHours();
        let minute = date.getMinutes();

        let dateFormat = growtype_form_date_time_data.date_format;

        function validateValue($this, dateText) {
            let parsedValue = Date.parse(dateText);
            try {
                if (isNaN(parsedValue)) {
                    throw null;
                }
                $.datepicker.parseDate(dateFormat, dateText);
            } catch (e) {
                $this.val('')
            }
        }

        $('.datetimepicker').datetimepicker({
            language: 'en',
            defaultDate: date,
            dateFormat: dateFormat,
            numberOfMonths: 1,
            hour: hour,
            minute: minute,
            minDate: new Date(),
            onClose: function (dateText, inst) {
                if ($(this).attr('name') === '_auction_dates_from') {
                    let tomorrow = new Date(dateText);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    tomorrow.toLocaleDateString();
                    $('.datetimepicker[name="_auction_dates_to"]').datetimepicker("option", "minDate", tomorrow);
                }
                validateValue($(this), dateText)
            },
            onSelectDate: function (dateText, inst) {
                validateValue($(this), dateText)
            },
            onChangeDateTime: function (dateText, inst) {
                validateValue($(this), dateText)
            }
        });
    }

    /**
     * Autonumeric
     */
    if ($(".autonumeric").length > 0) {
        $('.autonumeric').autoNumeric('init', {
            unformatOnSubmit: true,
            digitGroupSeparator: autoNumericdata.digitGroupSeparator,
            decimalCharacter: autoNumericdata.decimalCharacter,
            currencySymbol: autoNumericdata.currencySymbol,
            currencySymbolPlacement: autoNumericdata.currencySymbolPlacement,
            decimalPlacesOverride: autoNumericdata.decimalPlacesOverride,
            showWarnings: false,
            emptyInputBehavior: 'press',
            minimumValue: 0
        });
    }

    /**
     * File input
     */
    $('.e-wrapper .btn-img-remove').click(function () {
        let type = $(this).attr('data-type');
        let id = $(this).attr('data-id');
        let name = $(this).attr('data-name');
        let accept = $(this).attr('data-accept');
        let required = $(this).attr('data-required');
        $(this).closest('.input-file-wrapper').hide();
        $(this).closest('.e-wrapper').append('<input type="' + type + '" id="' + id + '" name="' + name + '"  accept="' + accept + '"  ' + required + '>');
    });
});
