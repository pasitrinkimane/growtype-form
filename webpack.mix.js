let mix = require('laravel-mix');

mix.setPublicPath('./public');
mix.setResourceRoot('./')

mix
    .sass('resources/styles/growtype-form.scss', 'styles')
    .sass('resources/styles/growtype-form-render.scss', 'styles')
    .sass('resources/styles/forms/login/main.scss', 'styles/forms/login')
    .sass('resources/styles/forms/signup/main.scss', 'styles/forms/signup');

mix
    .js('resources/scripts/growtype-form.js', 'scripts')
    .js('resources/scripts/growtype-form-render.js', 'scripts');

mix
    .copyDirectory('resources/plugins', 'public/plugins')
    .copyDirectory('resources/images', 'public/images')

mix
    .sourceMaps()
    .version();
