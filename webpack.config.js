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
            '@givewp/forms/types': path.resolve(__dirname, 'src/NextGen/DonationForm/resources/types.ts'),
            '@givewp/forms/propTypes': path.resolve(__dirname, 'src/NextGen/DonationForm/resources/propTypes.ts'),
            '@givewp/forms/app': path.resolve(__dirname, 'src/NextGen/DonationForm/resources/app'),
        },
    },
    entry: {
        donationFormBlock: srcPath('NextGen/DonationForm/Blocks/DonationFormBlock/resources/block.ts'),
        donationFormBlockStyle: srcPath(
            'NextGen/DonationForm/Blocks/DonationFormBlock/resources/editor/styles/index.scss'
        ),
        donationFormApp: srcPath('NextGen/DonationForm/resources/app/DonationFormApp.tsx'),
        donationFormRegistrars: srcPath('NextGen/DonationForm/resources/registrars/index.ts'),
        donationFormEmbed: srcPath('NextGen/DonationForm/resources/embed.ts'),
        donationFormEmbedInside: srcPath('NextGen/DonationForm/resources/embedInside.ts'),
        nextGenStripeGateway: srcPath('NextGen/Gateways/Stripe/NextGenStripeGateway/nextGenStripeGateway.tsx'),
        nextGenTestGateway: srcPath('NextGen/Gateways/NextGenTestGateway/nextGenTestGateway.tsx'),
        developerFormDesignCss: srcPath('NextGen/DonationForm/FormDesigns/DeveloperFormDesign/css/main.scss'),
        classicFormDesignCss: srcPath('NextGen/DonationForm/FormDesigns/ClassicFormDesign/css/main.scss'),
        classicFormDesignJs: srcPath('NextGen/DonationForm/FormDesigns/ClassicFormDesign/js/main.ts'),
        donationConfirmationReceiptApp: srcPath(
            'NextGen/DonationForm/resources/receipt/DonationConfirmationReceiptApp.tsx'
        ),
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
