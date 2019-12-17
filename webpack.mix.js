/**
 * External dependencies
 */
const mix = require('laravel-mix');
require('laravel-mix-polyfill');
const WebpackRTLPlugin = require( 'webpack-rtl-plugin' );
const wpPot = require( 'wp-pot' );

// JavaScript
mix.js('assets/src/js/frontend/give.js', 'js/give.js')
    .js('assets/src/js/frontend/give-stripe.js', 'js/give-stripe.js')
    .js('assets/src/js/frontend/give-stripe-checkout.js', 'js/give-stripe-checkout.js')
    .js('assets/src/js/admin/admin.js', 'js/admin.js')
    .js('blocks/load.js', 'js/blocks.js')
    .js('includes/admin/shortcodes/admin-shortcodes.js', 'js/admin-shortcodes.js')
    .js('assets/src/js/admin/plugin-deactivation-survey.js', 'js/plugin-deactivation-survey.js')
    .js('assets/src/js/admin/admin-add-ons.js', 'js/admin-add-ons.js');

// CSS
mix.sass('assets/src/css/frontend/give-frontend.scss', 'css/give-frontend.css')
    .sass('assets/src/css/admin/give-admin.scss', 'css/give-admin.css')
    .sass('assets/src/css/admin/give-admin-global.scss', 'css/give-admin-global.css')
    .sass('assets/src/css/admin/shortcodes.scss', 'css/admin-shortcode-buton.css')
    .sass('assets/src/css/admin/plugin-deactivation-survey.scss', 'css/plugin-deactivation-survey.css');

// Images 
mix.copyDirectory('assets/src/images', 'images');

// Set configuration
mix.setPublicPath('assets/dist')
    .polyfill()
    .browserSync('give.test');

if (mix.inProduction()) {
    mix.version();

    mix.webpackConfig({
        plugins: [
            new WebpackRTLPlugin( {
                suffix: '-rtl',
                minify: true,
            } )
        ],
    })

    // POT file.
	wpPot( {
		package: 'Give',
		domain: 'give',
		destFile: 'languages/give.pot',
		relativeTo: './',
		src: [ './**/*.php', '!./includes/libraries/**/*', '!./vendor/**/*' ],
		bugReport: 'https://github.com/impress-org/give/issues/new',
		team: 'GiveWP <info@givewp.com>',
	} );
}
