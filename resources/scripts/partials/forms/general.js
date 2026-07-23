import { ajaxFormSuccessEvent } from "../events/ajaxForm";

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
    $(document).off('change.growtype_gf_ajax_update').on(
        'change.growtype_gf_ajax_update',
        '[data-gf-ajax-update="true"]',
        function () {
            triggerAjaxFieldUpdate($(this));
        }
    );

    $(document).off('click.growtype_gf_ajax_update').on(
        'click.growtype_gf_ajax_update',
        '[data-gf-ajax-update="true"][data-gf-ajax-trigger="click"]',
        function () {
            triggerAjaxFieldUpdate($(this));
        }
    );
}

function displayFormAlert($form, message, type = 'danger') {
    $form.find('.form-response-alert').remove();
    if (message) {
        $form.prepend('<div class="alert alert-' + type + ' alert-dismissible fade show form-response-alert mb-4" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    }
}

function formGeneral() {
    window.growtype_form.postdata = {};

    bindAjaxFieldUpdates();

    $(document).off('submit.growtype_gf_ajax_submit').on('submit.growtype_gf_ajax_submit', '.growtype-form[data-ajax="true"], .growtype-form[data-ajax="1"]', function (event) {
        event.preventDefault();

        let $form = $(this);
        let action = $form.attr('data-ajax-action') || 'growtype_form_submission';

        window.growtype_form.postdata['form'] = $form.serialize()

        $.ajax({
            url: growtype_form.ajax_url,
            type: "post",
            data: {
                action: action,
                postdata: window.growtype_form.postdata
            }
        }).done(function (data) {
            $form.find('.form-response-alert').remove();

            if (data.success) {
                document.dispatchEvent(ajaxFormSuccessEvent());

                let $wrapper = $form.closest('.growtype-form-wrapper');
                let $success = $wrapper.siblings('.growtype-form-success');
                if (!$success.length) {
                    displayFormAlert($form, data.message, 'success');
                }
            } else {
                displayFormAlert($form, data.message, 'danger');
            }
        }).fail(function (xhr) {
            let message = 'Something went wrong. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            displayFormAlert($form, message, 'danger');
        }).always(function () {
            $form.find('button[type="submit"], button[data-action="submit"]').attr('disabled', false);
        });
    })
}

// ── Generic success handler for AJAX form submissions ───────────────
// Detects the submitted form (button disabled during submit) and swaps
// the form wrapper with a .growtype-form-success sibling, then auto-closes
// any Bootstrap modal after 2.5 s.
(function () {
    document.addEventListener('growtypeFormAjaxFormSuccess', function () {
        $('.growtype-form-wrapper').each(function () {
            var $wrapper = $(this);
            var $form = $wrapper.find('form[data-ajax="true"], form[data-ajax="1"]');
            if (!$form.length) return;

            var $submitBtn = $form.find('button[type="submit"]:disabled, button[data-action="submit"]:disabled');
            if (!$submitBtn.length) return;

            // Hide form, re-enable button
            $submitBtn.prop('disabled', false);

            // Show success sibling if present
            var $success = $wrapper.siblings('.growtype-form-success');
            if ($success.length) {
                $success.show();
            }

            // Auto-close Bootstrap modal
            var $modal = $wrapper.closest('.modal');
            if ($modal.length && typeof bootstrap !== 'undefined') {
                setTimeout(function () {
                    var bsModal = bootstrap.Modal.getInstance($modal[0]);
                    if (bsModal) bsModal.hide();
                }, 2500);
            }

            return false; // stop after first match
        });
    });
})();

export { formGeneral };
