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
            '@givewp/form-builder': srcPath('FormBuilder/resources/js/form-builder/src'),
        },
    },
    entry: {
        donationFormBlock: srcPath('DonationForms/Blocks/DonationFormBlock/resources/block.ts'),
        donationFormBlockApp: srcPath('DonationForms/Blocks/DonationFormBlock/resources/app/index.tsx'),
        donationFormApp: srcPath('DonationForms/resources/app/DonationFormApp.tsx'),
        donationFormRegistrars: srcPath('DonationForms/resources/registrars/index.ts'),
        donationFormEmbed: srcPath('DonationForms/resources/embed.ts'),
        donationFormEmbedInside: srcPath('DonationForms/resources/embedInside.ts'),
        stripePaymentElementGateway: srcPath(
            'PaymentGateways/Gateways/Stripe/StripePaymentElementGateway/stripePaymentElementGateway.tsx'
        ),
        testGateway: srcPath('PaymentGateways/Gateways/TestGateway/testGateway.tsx'),
        payPalStandardGateway: srcPath(
            'PaymentGateways/Gateways/PayPalStandard/resources/js/payPalStandardGateway.tsx'
        ),
        payPalCommerceGateway: srcPath('PaymentGateways/Gateways/PayPalCommerce/payPalCommerceGateway.tsx'),
        classicFormDesignCss: srcPath('DonationForms/FormDesigns/ClassicFormDesign/css/main.scss'),
        classicFormDesignJs: srcPath('DonationForms/FormDesigns/ClassicFormDesign/js/main.ts'),
        multiStepFormDesignCss: srcPath('DonationForms/FormDesigns/MultiStepFormDesign/css/main.scss'),
        multiStepFormDesignJs: srcPath('DonationForms/FormDesigns/MultiStepFormDesign/js/main.ts'),
        donationConfirmationReceiptApp: srcPath('DonationForms/resources/receipt/DonationConfirmationReceiptApp.tsx'),
        baseFormDesignCss: srcPath('DonationForms/resources/styles/base.scss'),
        formBuilderApp: srcPath('FormBuilder/resources/js/form-builder/src/index.tsx'),
        formBuilderRegistrars: srcPath('FormBuilder/resources/js/registrars/index.ts'),
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
