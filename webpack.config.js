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
        },
    },
    entry: {
        donationFormBlock: srcPath('NextGen/DonationForm/Blocks/DonationFormBlock/block.js'),
        donationFormBlockApp: srcPath('NextGen/DonationForm/Blocks/DonationFormBlock/app/DonationFormBlockApp.tsx'),
        donationFormRegistrars: srcPath('NextGen/DonationForm/Registrars/resources/registrars.ts'),
        nextGenStripeGateway: srcPath(
            'NextGen/Gateways/Stripe/NextGenStripeGateway/nextGenStripeGateway.tsx'
        ),
        nextGenTestGateway: srcPath('NextGen/Gateways/NextGenTestGateway/nextGenTestGateway.tsx'),
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
