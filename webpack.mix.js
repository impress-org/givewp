let mix = require('laravel-mix')

mix.setPublicPath('./assets/dist')
    .sass('assets/src/css/frontend/give-frontend.scss', 'css/give.css')

    .js('assets/src/js/frontend/give-stripe.js', 'js/')
    .sourceMaps(false);

mix.webpackConfig( {
    externals: {
        $: 'jQuery',
        jquery: 'jQuery',
    },
} );

mix.options( {
    // Don't perform any css url rewriting by default
    processCssUrls: false,
} );
