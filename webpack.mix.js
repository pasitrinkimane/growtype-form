let mix = require('laravel-mix');

mix
    .sass('resources/styles/growtype-form.scss', 'styles')

mix.setPublicPath('./public');
mix.setResourceRoot('./')

// mix.autoload({
//     jquery: ['$', 'window.jQuery']
// })

mix
    .js('resources/scripts/growtype-form.js', 'scripts')

mix
    .sourceMaps()
    .version();
