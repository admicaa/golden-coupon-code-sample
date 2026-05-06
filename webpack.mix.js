const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining Webpack build steps for
 | your Laravel application. We pin Vue compilation to version 2 so the
 | existing SFCs (Options API, Vue Router 3, Vuex 3, Vuetify 2) keep
 | building under Mix 6 / Webpack 5 without any frontend rewrite.
 |
 | The previous file referenced `path` without importing it (silently
 | relying on a global injected by older webpack tooling); the explicit
 | `require('path')` is required under Webpack 5.
 |
 */

mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js/'),
            Views: path.resolve(__dirname, 'resources/js/views/'),
        },
    },
    output: {
        chunkFilename: mix.inProduction()
            ? 'js/prod/chunks/[name]?id=[chunkhash].js'
            : 'js/dev/chunks/[name].js',
    },
});

mix
    .js('resources/js/app.js', 'public/js')
    .vue({ version: 2 })
    .sass('resources/sass/app.scss', 'public/css');

if (mix.inProduction()) {
    mix.version();
}
