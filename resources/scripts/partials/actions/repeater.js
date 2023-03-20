function repeater() {
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

    $('document').ready(function () {
        cloneReapeaterForm($('.repeater-fields a.btn-add'));
        removeRepeaterForm($('.btn-remove'));
    });
}

export {repeater};
