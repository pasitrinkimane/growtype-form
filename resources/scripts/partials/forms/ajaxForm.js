import {ajaxFormSuccessEvent} from "../events/ajaxForm";

function ajaxForm() {
    $('.growtype-form[data-ajax="true"]').submit(function (event) {
        event.preventDefault();

        let action = $(this).attr('data-ajax-action');

        $.ajax({
            url: growtype_form.ajax_url,
            type: "post",
            data: {
                action: action,
                postdata: $(this).serialize()
            }
        }).done(function (data) {
            if (data.success) {
                document.dispatchEvent(ajaxFormSuccessEvent());
            }
        });
    })
}

export {ajaxForm};
