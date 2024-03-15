const fs = require('fs');
const mix = require('laravel-mix');
const path = require('path');
const WebpackRTLPlugin = require('webpack-rtl-plugin');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

mix.setPublicPath('assets/dist')
    .sass('assets/src/css/frontend/give-frontend.scss', 'css/give.css')
    .sass('assets/src/css/admin/block-editor.scss', 'css/admin-block-editor.css')
    .sass('assets/src/css/admin/give-admin.scss', 'css/admin.css')
    .sass('assets/src/css/admin/give-admin-global.scss', 'css/admin-global.css')
    .sass('assets/src/css/admin/setup.scss', 'css/admin-setup.css')
    .sass('assets/src/css/admin/shortcodes.scss', 'css/admin-shortcode-button.css')
    .sass('assets/src/css/admin/plugin-deactivation-survey.scss', 'css/')
    .sass('assets/src/css/admin/widgets.scss', 'css/admin-widgets.css')
    .sass('assets/src/css/admin/paypal-commerce.scss', 'css/admin-paypal-commerce.css')
    .sass('src/Views/Form/Templates/Sequoia/assets/css/form.scss', 'css/give-sequoia-template.css')
    .sass('src/Views/Form/Templates/Classic/resources/css/form.scss', 'css/give-classic-template.css')
    .sass('src/MultiFormGoals/resources/css/common.scss', 'css/multi-form-goal-block.css')
    .sass('src/DonationSummary/resources/css/summary.scss', 'css/give-donation-summary.css')
    .sass('assets/src/css/admin/summer-sales-banner.scss', 'css/admin-summer-sales-banner.css')

    .js('assets/src/js/frontend/give.js', 'js/')
    .js('assets/src/js/frontend/give-stripe.js', 'js/')
    .js('assets/src/js/frontend/give-stripe-sepa.js', 'js/')
    .js('assets/src/js/frontend/give-stripe-becs.js', 'js/')
    .js('assets/src/js/frontend/paypal-commerce/index.js', 'js/paypal-commerce.js')
    .js('assets/src/js/admin/admin.js', 'js/')
    .js('assets/src/js/admin/admin-setup.js', 'js/')
    .js('assets/src/js/admin/plugin-deactivation-survey.js', 'js/')
    .js('assets/src/js/admin/admin-add-ons.js', 'js/')
    .js('assets/src/js/admin/admin-widgets.js', 'js/')
    .js('assets/src/js/admin/reports/app.js', 'js/admin-reports.js')
    .js('assets/src/js/admin/reports/widget.js', 'js/admin-reports-widget.js')
    .js('assets/src/js/admin/onboarding-wizard/index.js', 'js/admin-onboarding-wizard.js')
    .js('includes/admin/shortcodes/admin-shortcodes.js', 'js/')
    .js('src/Views/Form/Templates/Sequoia/assets/js/form.js', 'js/give-sequoia-template.js')
    .js('src/Views/Form/Templates/Classic/resources/js/form.js', 'js/give-classic-template.js')
    .js('src/DonorDashboards/resources/js/app/index.js', 'js/donor-dashboards-app.js')
    .js('src/DonorDashboards/resources/js/block/index.js', 'js/donor-dashboards-block.js')
    .js('src/Log/Admin/index.js', 'js/give-log-list-table-app.js')
    .js('src/MigrationLog/Admin/index.js', 'js/give-migrations-list-table-app.js')
    .js('src/DonationSummary/resources/js/summary.js', 'js/give-donation-summary.js')
    .js('src/Promotions/InPluginUpsells/resources/js/addons-admin-page.js', 'js/admin-upsell-addons-page.js')

    .ts('src/DonationForms/V2/resources/admin-donation-forms.tsx', 'js/give-admin-donation-forms.js')
    .ts('src/DonationForms/V2/resources/edit-v2form.tsx', 'js/give-edit-v2form.js')
    .ts('src/DonationForms/V2/resources/add-v2form.tsx', 'js/give-add-v2form.js')
    .ts('src/Donors/resources/admin-donors.tsx', 'js/give-admin-donors.js').
    ts('src/Donations/resources/index.tsx', 'js/give-admin-donations.js').
    ts('src/EventTickets/resources/admin/events-list-table.tsx',
        'js/give-admin-event-tickets.js').
    ts('src/EventTickets/resources/admin/event-details.tsx',
        'js/give-admin-event-tickets-details.js')
    .ts('src/Subscriptions/resources/admin-subscriptions.tsx', 'js/give-admin-subscriptions.js')
    .js('src/Promotions/InPluginUpsells/resources/js/sale-banner.js', 'js/admin-upsell-sale-banner.js')
    .ts('src/Promotions/InPluginUpsells/resources/js/donation-options.ts', 'js/donation-options.js')
    .ts('src/Promotions/InPluginUpsells/resources/js/payment-gateway.ts', 'js/payment-gateway.js')
    .ts('src/Promotions/WelcomeBanner/resources/js/index.tsx', 'js/welcome-banner.js')

    .react()
    .sourceMaps(false, 'source-map')

    .css('node_modules/@givewp/design-system-foundation/css/foundation.css', 'css/design-system/foundation.css')
    .after(() => {
        // Store the design system version in a file
        const packageJson = require('./node_modules/@givewp/design-system-foundation/package.json');
        const version = packageJson.version;

        if (!fs.existsSync(__dirname + '/assets/dist/css/design-system')) {
            fs.mkdirSync(__dirname + '/assets/dist/css/design-system');
        }

        fs.writeFileSync(__dirname + '/assets/dist/css/design-system/version', version);
    })

    .copyDirectory('assets/src/images', 'assets/dist/images')
    .copyDirectory('assets/src/fonts', 'assets/dist/fonts');

mix.webpackConfig({
    resolve: {
        alias: {
            '@givewp/components': path.resolve(__dirname, 'src/Views/Components/'),
            '@givewp/css': path.resolve(__dirname, 'assets/src/css/'),
            '@givewp/promotions': path.resolve(__dirname, 'src/Promotions/sharedResources/'),
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
});

mix.options({
    // Don't perform any css url rewriting by default
    processCssUrls: false,

    // Prevent LICENSE files from showing up in JS builds
    terser: {
        extractComments: (astNode, comment) => false,
        terserOptions: {
            format: {
                comments: false,
            },
        },
    },
});

if (mix.inProduction()) {
    mix.webpackConfig((webpack, config) => {
        return {
            plugins: [
                new WebpackRTLPlugin({
                    suffix: '-rtl',
                    minify: true,
                }),
                ...config.plugins,
            ],
        };
    });
}
