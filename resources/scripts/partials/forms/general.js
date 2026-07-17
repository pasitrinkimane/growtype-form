import {ajaxFormSuccessEvent} from "../events/ajaxForm";

function parseAjaxParamsFromDataset($element) {
    const params = {};
    const node = $element.get(0);

    if (!node || !node.attributes) {
        return params;
    }

    Array.from(node.attributes).forEach((attr) => {
        if (!attr || !attr.name || !attr.name.startsWith('data-gf-param-')) {
            return;
        }

        const key = attr.name.replace('data-gf-param-', '').trim();
        if (!key) {
            return;
        }

        params[key] = attr.value;
    });

    const paramsRaw = $element.attr('data-gf-ajax-params');
    if (paramsRaw) {
        try {
            const parsed = JSON.parse(paramsRaw);
            if (parsed && typeof parsed === 'object') {
                Object.assign(params, parsed);
            }
        } catch (e) {
            // Ignore invalid JSON and keep data-gf-param-* values.
        }
    }

    return params;
}

function resolveAjaxFieldValue($trigger) {
    if ($trigger.attr('data-gf-ajax-value') !== undefined) {
        return $trigger.attr('data-gf-ajax-value');
    }

    if ($trigger.is(':checkbox')) {
        return $trigger.is(':checked') ? '1' : '0';
    }

    if ($trigger.is(':radio')) {
        if (!$trigger.is(':checked')) {
            return null;
        }
        return $trigger.val();
    }

    if ($trigger.is('input, textarea, select')) {
        return $trigger.val();
    }

    return $trigger.attr('data-value') ?? $trigger.data('value') ?? '';
}

function triggerAjaxFieldUpdate($trigger) {
    const action = $trigger.attr('data-gf-ajax-action');
    if (!action) {
        return;
    }

    const fieldName = $trigger.attr('data-gf-ajax-field')
        || $trigger.attr('name')
        || $trigger.attr('data-name');

    const fieldValue = resolveAjaxFieldValue($trigger);
    if (!fieldName || fieldValue === null || fieldValue === undefined) {
        return;
    }

    const nonceKey = $trigger.attr('data-gf-ajax-nonce-key') || 'nonce';
    const nonceValue = $trigger.attr('data-gf-ajax-nonce')
        || window.growtypeProfileUpload?.nonce
        || window.growtype_form?.field_update_nonce
        || '';

    const requestData = {
        action: action,
        field: fieldName,
        value: fieldValue,
        ...parseAjaxParamsFromDataset($trigger),
    };

    if (nonceValue) {
        requestData[nonceKey] = nonceValue;
    }

    if ($trigger.attr('data-gf-ajax-form-key')) {
        requestData.form_key = $trigger.attr('data-gf-ajax-form-key');
    }

    $.ajax({
        url: window.growtype_form?.ajax_url,
        type: "post",
        data: requestData
    }).done(function (response) {
        document.dispatchEvent(new CustomEvent('growtypeFormFieldUpdateSuccess', {
            detail: {
                trigger: $trigger.get(0),
                response: response
            }
        }));
    });
}

function bindAjaxFieldUpdates() {
    $(document).on(
        'change',
        '[data-gf-ajax-update="true"]',
        function () {
            triggerAjaxFieldUpdate($(this));
        }
    );

    $(document).on(
        'click',
        '[data-gf-ajax-update="true"][data-gf-ajax-trigger="click"]',
        function () {
            triggerAjaxFieldUpdate($(this));
        }
    );
}

function formGeneral() {
    window.growtype_form.postdata = {};

    bindAjaxFieldUpdates();

    $('.growtype-form[data-ajax="true"]').submit(function (event) {
        event.preventDefault();

        let action = $(this).attr('data-ajax-action');

        window.growtype_form.postdata['form'] = $(this).serialize()

        $.ajax({
            url: growtype_form.ajax_url,
            type: "post",
            data: {
                action: action,
                postdata: window.growtype_form.postdata
            }
        }).done(function (data) {
            if (data.success) {
                document.dispatchEvent(ajaxFormSuccessEvent());
            }
        });
    })
}

export {formGeneral};
