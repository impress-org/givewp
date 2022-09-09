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
        },
    },
    entry: {
        donationFormBlock: srcPath('NextGen/DonationForm/Blocks/DonationFormBlock/resources/block.ts'),
        donationFormBlockStyle: srcPath('NextGen/DonationForm/Blocks/DonationFormBlock/resources/editor/styles/index.scss'),
        donationFormBlockApp: srcPath('NextGen/DonationForm/Blocks/DonationFormBlock/resources/app/DonationFormBlockApp.tsx'),
        donationFormRegistrars: srcPath('NextGen/DonationForm/Registrars/resources/registrars.ts'),
        donationFormEmbed: srcPath('NextGen/DonationForm/resources/embed.ts'),
        donationFormEmbedInside: srcPath('NextGen/DonationForm/resources/embedInside.ts'),
        nextGenStripeGateway: srcPath(
            'NextGen/Gateways/Stripe/NextGenStripeGateway/nextGenStripeGateway.tsx'
        ),
        nextGenTestGateway: srcPath('NextGen/Gateways/NextGenTestGateway/nextGenTestGateway.tsx'),
        classicTemplateJs: srcPath('NextGen/DonationForm/FormTemplates/ClassicFormTemplate/js/template.ts'),
        classicTemplateCss: srcPath('NextGen/DonationForm/FormTemplates/ClassicFormTemplate/css/template.scss'),
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
