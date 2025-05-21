export function formAuth() {
    $('.growtype-form-auth .btn-link').click(function (e) {
        e.preventDefault();

        const type = $(this).attr('data-type');
        if (!type) return;

        const wrapper = $(this).closest('.growtype-form-wrapper');

        // Get normalized path without trailing slash
        let path = window.location.pathname.replace(/\/$/, '');
        const search = window.location.search;

        let newPath;

        if (type === 'login') {
            wrapper.fadeOut().promise().done(function () {
                $(this).removeClass('is-active');
                $('.growtype-form-wrapper[data-name="login"]').fadeIn().promise().done(function () {
                    $(this).addClass('is-active');
                });
            });

            newPath = path.replace(/\/signup$/, '/login');
            // If not already /signup, force it to /login
            if (path !== '/signup' && path !== '/signup') {
                newPath = '/login';
            }
        }

        if (type === 'signup') {
            wrapper.fadeOut().promise().done(function () {
                $(this).removeClass('is-active');
                $('.growtype-form-wrapper[data-name="signup"]').fadeIn().promise().done(function () {
                    $(this).addClass('is-active');
                });
            });

            newPath = path.replace(/\/login$/, '/signup');
            // If not already /login, force it to /signup
            if (path !== '/login' && path !== '/login') {
                newPath = '/signup';
            }
        }

        // Re-append search/query params if they exist
        const newUrl = newPath + search;
        history.replaceState(null, '', newUrl);
    });
}
