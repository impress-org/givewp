const path = require('path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

/**
 * This is a custom webpack configuration, consumed by Laravel Mix.
 */
module.exports = {
    resolve: {
        alias: {
            '@givewp/components': path.resolve(__dirname, 'src/Views/Components/'),
            '@givewp/css': path.resolve(__dirname, 'assets/src/css/'),
            '@givewp/promotions': path.resolve(__dirname, 'src/Promotions/sharedResources/'),
            '@givewp/src': path.resolve(__dirname, 'src/'),
        },
    },
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
}
