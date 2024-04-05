/**
 * External Dependencies
 */
const path = require('path');

/**
 * WordPress Dependencies
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');

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
        adminBlocks: path.resolve(process.cwd(), 'blocks', 'load.js'),
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
