const mix = require('laravel-mix');
const wpPot = require('wp-pot');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const donationFormBlockPath = 'src/NextGen/DonationForm/Blocks/DonationFormBlock';

/**
 * Keep block builds within their domain for blocks.json to work properly
 */
mix
    /**
     * DonationFormBlock - editor
     */
    .js(`${donationFormBlockPath}/block.js`, `${donationFormBlockPath}/build/editor.js`)

    /**
     * DonationFormBlock - view
     */
    .js(`${donationFormBlockPath}/app/App.jsx`, `${donationFormBlockPath}/build/view.js`);

/**
 * Extend the config to use DependencyExtractionWebpackPlugin for WP scripts asset dependencies
 */
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

if (mix.inProduction()) {
    wpPot({
        package: 'Give - Next Gen',
        domain: 'give',
        destFile: 'languages/give.pot',
        relativeTo: './',
        bugReport: 'https://github.com/impress-org/give-next-gen/issues/new',
        team: 'GiveWP <info@givewp.com>',
    });
}
