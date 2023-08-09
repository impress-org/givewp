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
            //TODO - enable aliases when files are available in GiveWP 3.0. Will also need to update tsconfig to match.
            //'@givewp/forms/types': srcPath('DonationForms/resources/types.ts'),
            //'@givewp/forms/propTypes': srcPath('DonationForms/resources/propTypes.ts'),
            //'@givewp/forms/app': srcPath('DonationForms/resources/app'),
            //'@givewp/form-builder': srcPath('FormBuilder/resources/js/form-builder/src'),
        },
    },
    entry: {
        testGateway: srcPath('PaymentGateways/Gateways/TestGateway/testGateway.tsx'),
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
