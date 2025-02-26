/**
 * External Dependencies
 */
const path = require('path');

/**
 * WordPress Dependencies
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');

const legacyConfig = require('./webpack.config.js');


const legacyScriptsEntry = {
    "assets/dist/js/give": assetPath('src/js/frontend/give.js'),
    "assets/dist/js/give-stripe": assetPath('src/js/frontend/give-stripe.js'),
    "assets/dist/js/give-stripe-sepa": assetPath('src/js/frontend/give-stripe-sepa.js'),
    "assets/dist/js/give-stripe-becs": assetPath('src/js/frontend/give-stripe-becs.js'),
    "assets/dist/js/paypal-commerce": assetPath('src/js/frontend/paypal-commerce/index.js'),
    "assets/dist/js/admin": assetPath('src/js/admin/admin.js'),
    "assets/dist/js/admin-setup": assetPath('src/js/admin/admin-setup.js'),
    "assets/dist/js/plugin-deactivation-survey": assetPath('src/js/admin/plugin-deactivation-survey.js'),
    "assets/dist/js/admin-add-ons": assetPath('src/js/admin/admin-add-ons.js'),
    "assets/dist/js/admin-widgets": assetPath('src/js/admin/admin-widgets.js'),
    "assets/dist/js/admin-reports": assetPath('src/js/admin/reports/app.js'),
    "assets/dist/js/admin-reports-widget": assetPath('src/js/admin/reports/widget.js'),
    "assets/dist/js/admin-onboarding-wizard": assetPath('src/js/admin/onboarding-wizard/index.js'),
    "assets/dist/js/admin-shortcodes": includesPath('admin/shortcodes/admin-shortcodes.js'),
    "assets/dist/js/give-sequoia-template": srcPath('Views/Form/Templates/Sequoia/assets/js/form.js'),
    "assets/dist/js/give-classic-template": srcPath('Views/Form/Templates/Classic/resources/js/form.js'),
    "assets/dist/js/donor-dashboards-app": srcPath('DonorDashboards/resources/js/app/index.js'),
    "assets/dist/js/donor-dashboards-block": srcPath('DonorDashboards/resources/js/block/index.js'),
    "assets/dist/js/give-log-list-table-app": srcPath('Log/Admin/index.js'),
    "assets/dist/js/give-migrations-list-table-app": srcPath('MigrationLog/Admin/index.js'),
    "assets/dist/js/give-donation-summary": srcPath('DonationSummary/resources/js/summary.js'),
    "assets/dist/js/admin-upsell-addons-page": srcPath('Promotions/InPluginUpsells/resources/js/addons-admin-page.js'),
    "assets/dist/js/give-donation-forms-load-async-data": srcPath('DonationForms/AsyncData/resources/loadAsyncData.js'),
    "assets/dist/js/give-admin-donation-forms": srcPath('DonationForms/V2/resources/admin-donation-forms.tsx'),
    "assets/dist/js/give-edit-v2form": srcPath('DonationForms/V2/resources/edit-v2form.tsx'),
    "assets/dist/js/give-add-v2form": srcPath('DonationForms/V2/resources/add-v2form.tsx'),
    "assets/dist/js/give-admin-donors": srcPath('Donors/resources/admin-donors.tsx'),
    "assets/dist/js/give-admin-donations": srcPath('Donations/resources/index.tsx'),
    "assets/dist/js/give-admin-event-tickets": srcPath('EventTickets/resources/admin/events-list-table.tsx'),
    "assets/dist/js/give-admin-event-tickets-details": srcPath('EventTickets/resources/admin/event-details.tsx'),
    "assets/dist/js/give-admin-subscriptions": srcPath('Subscriptions/resources/admin-subscriptions.tsx'),
    "assets/dist/js/admin-upsell-sale-banner": srcPath('Promotions/InPluginUpsells/resources/js/sale-banner.js'),
    "assets/dist/js/donation-options": srcPath('Promotions/InPluginUpsells/resources/js/donation-options.ts'),
    "assets/dist/js/payment-gateway": srcPath('Promotions/InPluginUpsells/resources/js/payment-gateway.ts'),
    "assets/dist/js/welcome-banner": srcPath('Promotions/WelcomeBanner/resources/js/index.tsx'),
}

/**
 * Custom config
 */
module.exports = {
    ...defaultConfig,
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            '@givewp/forms/types': srcPath('DonationForms/resources/types.ts'),
            '@givewp/forms/propTypes': srcPath('DonationForms/resources/propTypes.ts'),
            '@givewp/forms/app': srcPath('DonationForms/resources/app'),
            '@givewp/forms/registrars': srcPath('DonationForms/resources/registrars'),
            '@givewp/forms/shared': srcPath('DonationForms/resources/shared'),
            '@givewp/form-builder': srcPath('FormBuilder/resources/js/form-builder/src'),
            '@givewp/form-builder/registrars': srcPath('FormBuilder/resources/js/registrars/index.ts'),
            '@givewp/components': srcPath('Views/Components/'),
            ...legacyConfig.resolve.alias,
        },
    },
    entry: {
        donationFormBlock: srcPath('DonationForms/Blocks/DonationFormBlock/resources/block.ts'),
        donationFormBlockApp: srcPath('DonationForms/Blocks/DonationFormBlock/resources/app/index.tsx'),
        donationFormApp: srcPath('DonationForms/resources/app/DonationFormApp.tsx'),
        donationFormRegistrars: srcPath('DonationForms/resources/registrars/index.ts'),
        donationFormEmbed: srcPath('DonationForms/resources/embed.ts'),
        donationFormEmbedInside: srcPath('DonationForms/resources/embedInside.ts'),
        eventTicketsBlock: srcPath('EventTickets/resources/blocks/index.ts'),
        eventTicketsTemplate: srcPath('EventTickets/resources/templates/index.ts'),
        stripePaymentElementGateway: srcPath(
            'PaymentGateways/Gateways/Stripe/StripePaymentElementGateway/stripePaymentElementGateway.tsx'
        ),
        stripePaymentElementFormBuilder: srcPath(
            'PaymentGateways/Gateways/Stripe/StripePaymentElementGateway/resources/js/index.tsx'
        ),
        testGateway: srcPath('PaymentGateways/Gateways/TestGateway/testGateway.tsx'),
        testOffsiteGateway: srcPath('PaymentGateways/Gateways/TestOffsiteGateway/testOffsiteGateway.tsx'),
        offlineGateway: srcPath('PaymentGateways/Gateways/Offline/resources/offlineGateway.tsx'),
        offlineGatewayFormBuilder: srcPath('PaymentGateways/Gateways/Offline/resources/formBuilder/index.tsx'),
        payPalStandardGateway: srcPath(
            'PaymentGateways/Gateways/PayPalStandard/resources/js/payPalStandardGateway.tsx'
        ),
        payPalCommerceGateway: srcPath('PaymentGateways/Gateways/PayPalCommerce/payPalCommerceGateway.tsx'),
        classicFormDesignCss: srcPath('DonationForms/FormDesigns/ClassicFormDesign/css/main.scss'),
        classicFormDesignJs: srcPath('DonationForms/FormDesigns/ClassicFormDesign/js/main.ts'),
        multiStepFormDesignCss: srcPath('DonationForms/FormDesigns/MultiStepFormDesign/css/main.scss'),
        twoPanelStepsFormLayoutCss: srcPath('DonationForms/FormDesigns/TwoPanelStepsFormLayout/css/main.scss'),
        donationConfirmationReceiptApp: srcPath('DonationForms/resources/receipt/DonationConfirmationReceiptApp.tsx'),
        baseFormDesignCss: srcPath('DonationForms/resources/styles/base.scss'),
        formBuilderApp: srcPath('FormBuilder/resources/js/form-builder/src/index.tsx'),
        formBuilderRegistrars: srcPath('FormBuilder/resources/js/registrars/index.ts'),
        formTaxonomySettings: srcPath('FormTaxonomies/resources/form-builder/index.tsx'),
        adminBlocks: path.resolve(process.cwd(), 'blocks', 'load.js'),
        ...legacyScriptsEntry
    },
};

/**
 * Helper for getting the path to the src directory.
 *
 * @param {string} relativePath
 * @returns {string}
 */
function srcPath(relativePath) {
    return path.resolve(process.cwd(), 'src', relativePath);
}

/**
 * Helper for getting the path to the assets directory.
 *
 * @param {string} relativePath
 * @returns {string}
 */
function assetPath(relativePath) {
    return path.resolve(process.cwd(), 'assets', relativePath);
}

/**
 * Helper for getting the path to the includes directory.
 *
 * @param {string} relativePath
 * @returns {string}
 */
function includesPath(relativePath) {
    return path.resolve(process.cwd(), 'includes', relativePath);
}
