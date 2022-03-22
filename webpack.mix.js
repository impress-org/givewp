const mix = require('laravel-mix');
const wpPot = require('wp-pot');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

mix.setPublicPath('public')
    .sourceMaps(false)

    // admin assets
    .js('src/NextGen/resources/js/admin/give-next-gen-admin.js', 'public/js/')
    .sass('src/NextGen/resources/css/admin/give-next-gen-admin.scss', 'public/css')

    // public assets
    .js('src/NextGen/resources/js/frontend/give-next-gen.js', 'public/js/')
    .sass('src/NextGen/resources/css/frontend/give-next-gen-frontend.scss', 'public/css')

    // Donation Form Blocks
    .js('src/NextGen/DonationForm/Blocks/DonationFormBlock/index.js', 'public/js/give-next-gen-donation-form-block.js')
    .sass(
        'src/NextGen/DonationForm/Blocks/DonationFormBlock/index.scss',
        'public/css/give-next-gen-donation-form-block.css'
    )

    // images
    .copy('src/NextGen/resources/images/*.{jpg,jpeg,png,gif}', 'public/images');

mix.webpackConfig({
    plugins: [
        /*
         * Transform script dependencies only for following external libraries:
         * - @wordpress/
         * - jquery
         * - lodash, lodash-es
         */
        new DependencyExtractionWebpackPlugin({
            useDefaults: false,
            requestToExternal: (request) => {
                const WORDPRESS_NAMESPACE = '@wordpress/';

                if (request.startsWith(WORDPRESS_NAMESPACE)) {
                    return [
                        'wp',

                        /* Transform @wordpress dependencies:
                         * - request @wordpress/api-fetch becomes [ 'wp', 'apiFetch' ]
                         * - request @wordpress/i18n becomes [ 'wp', 'i18n' ]
                         */
                        request
                            .substring(WORDPRESS_NAMESPACE.length)
                            .replace(/-([a-z])/g, (_, letter) => letter.toUpperCase()),
                    ];
                } else if (['lodash', 'lodash-es'].includes(request)) {
                    return 'lodash';
                } else if (request === 'jquery') {
                    return 'jQuery';
                }
            },
            requestToHandle: (request) => {
                const WORDPRESS_NAMESPACE = '@wordpress/';

                if (request === 'lodash-es') {
                    return 'lodash';
                }

                if (request.startsWith(WORDPRESS_NAMESPACE)) {
                    return 'wp-' + request.substring(WORDPRESS_NAMESPACE.length);
                }
            },
        }),
    ],
});

if ( mix.inProduction() ) {
	wpPot( {
		package: 'Give - Next Gen',
		domain: 'give',
		destFile: 'languages/give.pot',
		relativeTo: './',
		bugReport: 'https://github.com/impress-org/give-next-gen/issues/new',
		team: 'GiveWP <info@givewp.com>',
	} );
}
