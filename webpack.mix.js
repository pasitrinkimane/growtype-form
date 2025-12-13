let mix = require('laravel-mix');

mix.setPublicPath('./public');
mix.setResourceRoot('./')

mix
    .sass('resources/styles/growtype-form.scss', 'styles')
    .sass('resources/styles/growtype-form-render.scss', 'styles')
    .sass('resources/styles/forms/newsletter/index.scss', 'styles/forms/newsletter')
    .sass('resources/styles/forms/auth/index.scss', 'styles/forms/auth');

mix
    .js('resources/scripts/growtype-form.js', 'scripts')
    .js('resources/scripts/growtype-form-render.js', 'scripts')
    .js('resources/scripts/growtype-form-profile-edit.js', 'scripts')
    .js('resources/scripts/growtype-form-profile-security.js', 'scripts');

mix
    .sourceMaps()
    .version();
