function formNewsletter() {
    $('.growtype-form-newsletter').submit(function () {
        event.preventDefault();

        let form = $(this);
        let formData = form.serializeArray();
        let submitBtn = $(this).find('button[type="submit"]');

        formData.push({name: "action", value: 'growtype_form_newsletter_submission'});

        form.find('.status-message').fadeOut().promise().done(function () {
            $(this).removeClass('alert-danger alert-success');
        });

        var formUrl = $(this).attr('action');

        submitBtn.attr('disabled', true);

        $.ajax({
            type: "POST",
            url: formUrl.length > 0 ? formUrl : window.ajax_object.ajaxurl,
            data: formData,
            success: function (data) {
                form.find('.status-message')
                    .html(data.message)
                    .addClass('alert-success')
                    .fadeIn();

                submitBtn.attr('disabled', false);

                setTimeout(function () {
                    form.find('.status-message').fadeOut();
                }, 1500)
            },
            error: function (data) {
                form.find('.status-message')
                    .html(data.responseJSON.message)
                    .addClass('alert-danger')
                    .fadeIn();

                submitBtn.attr('disabled', false);

                setTimeout(function () {
                    form.find('.status-message').fadeOut();
                }, 1500)
            }
        });
    });
}

export {formNewsletter};
