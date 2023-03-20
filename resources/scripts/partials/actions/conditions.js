function conditions() {
    window.growtype_form.conditions = [];
    $('.growtype-form .e-wrapper').each(function (index, element) {
        if ($(element).attr('data-conditions').length > 0) {
            let conditions = JSON.parse($(element).attr('data-conditions'));
            let target = $(element).attr('data-name');

            conditions.map(function (element, index) {
                let name = element['name'];
                let condition = element['condition'];
                let value = element['value'];
                window.growtype_form.conditions[name] = {
                    'condition': condition,
                    'value': value,
                    'target': target,
                }
            })
        }
    });

    $(document).ready(function () {
        if (typeof window.growtype_form.conditions === 'object') {
            $('.growtype-form input[type="checkbox"]').change(function () {
                let name = $(this).attr('name');

                if (window.growtype_form.conditions[name] !== undefined) {
                    if (window.growtype_form.conditions[name]['value'] = 'checked') {
                        if (window.growtype_form.conditions[name]['condition'] === 'visible') {
                            if ($(this).is(':checked')) {
                                $('.e-wrapper[data-name="' + window.growtype_form.conditions[name]['target'] + '"]').show();
                            } else {
                                $('.e-wrapper[data-name="' + window.growtype_form.conditions[name]['target'] + '"]').hide();
                            }
                        }
                    }
                }
            })
        }
    });
}

export {conditions};
