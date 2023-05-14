import {ajaxFormSuccessEvent} from "../events/ajaxForm";

function ajaxForm() {
    window.growtype_form.postdata = {};
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

export {ajaxForm};
