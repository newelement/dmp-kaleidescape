const mix = require('laravel-mix');

mix.js('resources/assets/js/nowplaying.js', 'publishable/assets/js').sass(
    'resources/assets/sass/plugin.scss',
    'publishable/assets/css'
);
