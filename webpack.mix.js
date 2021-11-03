const mix = require('laravel-mix');
const path = require('path');
const WebpackRTLPlugin = require('webpack-rtl-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');

mix.setPublicPath('assets/dist')
    .sass('assets/src/css/frontend/give-frontend.scss', 'css/give.css')
    .sass('assets/src/css/admin/give-admin.scss', 'css/admin.css')
    .sass('assets/src/css/admin/give-admin-global.scss', 'css/admin-global.css')
    .sass('assets/src/css/admin/setup.scss', 'css/admin-setup.css')
    .sass('assets/src/css/admin/shortcodes.scss', 'css/admin-shortcode-button.css')
    .sass('assets/src/css/admin/plugin-deactivation-survey.scss', 'css/')
    .sass('assets/src/css/admin/widgets.scss', 'css/admin-widgets.css')
    .sass('assets/src/css/admin/paypal-commerce.scss', 'css/admin-paypal-commerce.css')
    .sass('src/Views/Form/Templates/Sequoia/assets/css/form.scss', 'css/give-sequoia-template.css')
    .sass('src/MultiFormGoals/resources/css/common.scss', 'css/multi-form-goal-block.css')

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
    .js('blocks/load.js', 'js/gutenberg.js')
    .js('src/Views/Form/Templates/Sequoia/assets/js/form.js', 'js/give-sequoia-template.js')
    .js('src/DonorDashboards/resources/js/app/index.js', 'js/donor-dashboards-app.js')
    .js('src/DonorDashboards/resources/js/block/index.js', 'js/donor-dashboards-block.js')
    .js('src/Log/Admin/index.js', 'js/give-log-list-table-app.js')
    .js('src/MigrationLog/Admin/index.js', 'js/give-migrations-list-table-app.js')
    .babelConfig({
        "presets": [
            "@babel/preset-env",
            [
                "@babel/preset-react",
                {
                    "runtime": "classic"
                },
            ],
        ],
    })
    .sourceMaps(false)

    .copyDirectory('assets/src/tcpdf-fonts', 'vendor/tecnickcom/tcpdf/fonts')
    .copyDirectory('assets/src/images', 'assets/dist/images')
    .copyDirectory('assets/src/fonts', 'assets/dist/fonts');

mix.webpackConfig({
    externals: {
        $: 'jQuery',
        jquery: 'jQuery',
        lodash: 'lodash',
        '@wordpress/i18n': 'wp.i18n',
    },
    resolve: {
        alias: {
            '@givewp/components': path.resolve(__dirname, 'src/Views/Components/'),
        },
    },
});

mix.options({
    // Don't perform any css url rewriting by default
    processCssUrls: false,
});

if (mix.inProduction()) {
    mix.webpackConfig({
        plugins: [
            new WebpackRTLPlugin({
                suffix: '-rtl',
                minify: true,
            }),
            new CleanWebpackPlugin({
                // We clean up he tcpdf directory in the vendor to prevent it from bloating the release file size
                cleanOnceBeforeBuildPatterns: [path.join(process.cwd(), 'vendor/tecnickcom/tcpdf/fonts/*')],
            }),
        ],
    });
}
