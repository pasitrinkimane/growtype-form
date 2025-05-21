function conditions() {

    /**
     * Collect all conditions from the form
     * @type {*[]}
     */
    window.growtype_form.conditions = [];
    $('.growtype-form .e-wrapper, .growtype-form .b-wrapper').each(function (index, element) {
        if ($(element).attr('data-conditions') && $(element).attr('data-conditions').length > 0) {
            let conditions = JSON.parse($(element).attr('data-conditions'));
            let target = $(element).attr('data-name');

            Object.entries(conditions).map(function (element, index) {
                let condition = {
                    'name': element[1]['name'],
                    'condition': element[1]['condition'] ?? '',
                    'value': element[1]['value'],
                    'target': target,
                    'label': element[1]['label'] ?? ''
                };

                window.growtype_form.conditions.push(condition)
            })
        }
    });

    if (typeof window.growtype_form.conditions === 'object') {
        /**
         * on form load
         */
        $('.form-wrapper').each(function (index, element) {
            updateConditionalValues($(element))
        })

        /**
         * On select
         */
        $('.growtype-form select').change(function () {
            let form = $(this).closest('.form-wrapper');

            updateConditionalValues(form)

            /**
             * Reset conditional checkboxes
             */
            if (form.find('input[type="checkbox"]:checked:hidden').length > 0) {
                form.find('input[type="checkbox"]:checked:hidden').each(function (index, element) {
                    $(element).prop('checked', false);
                });

                updateConditionalValues(form)
            }
        });

        /**
         * On check
         */
        $('.growtype-form input[type="checkbox"]').change(function () {
            let form = $(this).closest('.form-wrapper');

            updateConditionalValues(form)
        });
    }

    function updateConditionalValues(form) {
        if (window.growtype_form.conditions.length > 0) {
            form.find('[data-conditions]').each(function (index, element) {
                if ($(element).attr('data-conditions').length > 0) {
                    $(element).hide();
                }
            })

            window.growtype_form.conditions.map(function (element, index) {
                let selectedElement = $('[name="' + element['name'] + '"]');
                let componentValue = selectedElement.val() === null ? '' : selectedElement.val();

                /**
                 * Check if checkbox is checked
                 */
                if (selectedElement.attr('type') === 'checkbox' && selectedElement.is(':checked')) {
                    componentValue = 'checked';
                }

                let target = form.find('.b-wrapper[data-name="' + element['target'] + '"], .e-wrapper[data-name="' + element['target'] + '"]');

                if (componentValue === element['value']) {
                    let isRequired = target.find('.form-control').attr('required') !== undefined ? true : false;
                    let label = target.find('.form-label');
                    let labelText = label.text();
                    let labelTextInitial = target.attr('data-initial-label');

                    if (!window.growtype_form.conditions[index]['initial_label'] && labelTextInitial !== undefined) {
                        if (isRequired) {
                            labelTextInitial = labelTextInitial.replace("*", '<span class="required" aria-required="true">*</span>');
                        }

                        window.growtype_form.conditions[index]['initial_label'] = labelTextInitial;
                    }

                    if (element['condition'] === 'replace') {
                        target.attr('data-initial-label', labelText);
                        label.html(element['label'] + (isRequired ? '<span class="required" aria-required="true">*</span>' : ''));
                    } else {
                        if (window.growtype_form.conditions[index]['initial_label']) {
                            label.html(window.growtype_form.conditions[index]['initial_label']);
                        }
                    }

                    target.show();
                }
            })
        }
    }
}

export {conditions};
