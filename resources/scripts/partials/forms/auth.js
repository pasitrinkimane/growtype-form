function formAuth() {
    $('.growtype-form-auth .btn-link').click(function (e) {
        if ($(this).attr('data-type') && $(this).attr('data-type').length > 0) {
            event.preventDefault();

            var currentUrl = window.location.href;
            var newUrl;

            if ($(this).attr('data-type') === 'login') {
                $(this).closest('.growtype-form-wrapper').fadeOut().promise().done(function () {
                    $(this).removeClass('is-active');
                    $('.growtype-form-wrapper[data-name="login"]').fadeIn().promise().done(function () {
                        $(this).addClass('is-active');
                    });
                })

                newUrl = currentUrl.replace("/signup/", "/login/");
            }

            if ($(this).attr('data-type') === 'signup') {
                $(this).closest('.growtype-form-wrapper').fadeOut().promise().done(function () {
                    $(this).removeClass('is-active');
                    $('.growtype-form-wrapper[data-name="signup"]').fadeIn().promise().done(function () {
                        $(this).addClass('is-active');
                    });
                })

                newUrl = currentUrl.replace("/login/", "/signup/");
            }

            history.replaceState(null, null, newUrl);
        }
    });
}

export {formAuth};
