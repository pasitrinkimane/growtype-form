let mix = require('laravel-mix');

mix.setPublicPath('./public');
mix.setResourceRoot('./')

mix
    .sass('resources/styles/growtype-form.scss', 'styles')
    .sass('resources/styles/growtype-form-render.scss', 'styles');

mix
    .js('resources/scripts/growtype-form.js', 'scripts')
    .js('resources/scripts/growtype-form-render.js', 'scripts');

mix
    .copyDirectory('resources/plugins', 'public/plugins')

mix
    .sourceMaps()
    .version();
