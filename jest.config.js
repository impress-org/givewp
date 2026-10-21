const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/jest-unit.config');

const srcPath = (relativePath) => path.resolve(__dirname, 'src', relativePath);

/**
 * `jest-environment-jsdom` ships inside `@wordpress/scripts`, not at the top of `node_modules`.
 */
const jsdomEnvironment = require.resolve('jest-environment-jsdom', {
    paths: [path.dirname(require.resolve('@wordpress/scripts/package.json'))],
});

/**
 * Jest unit tests for pure TypeScript (adapters, helpers). Runs with `npm run test:unit:js`.
 *
 * The `@givewp/*` aliases mirror `webpack.config.js` so tests import code the same way the bundles do.
 * Style imports are stubbed by the `@wordpress/jest-preset-default` preset.
 *
 * @since TBD
 */
module.exports = {
    ...defaultConfig,
    testEnvironment: jsdomEnvironment,
    // jsdom picks packages' browser builds, and some of those (uuid, used by @wordpress/components) are ES modules.
    testEnvironmentOptions: {customExportConditions: ['node', 'node-addons']},
    roots: ['<rootDir>/src'],
    // The Form Builder test predates this config, was never run, and needs the builder's window data.
    testPathIgnorePatterns: ['/node_modules/', '<rootDir>/src/FormBuilder/'],
    moduleNameMapper: {
        '^@givewp/forms/types$': srcPath('DonationForms/resources/types.ts'),
        '^@givewp/forms/propTypes$': srcPath('DonationForms/resources/propTypes.ts'),
        '^@givewp/forms/app(.*)$': srcPath('DonationForms/resources/app$1'),
        '^@givewp/forms/registrars(.*)$': srcPath('DonationForms/resources/registrars$1'),
        '^@givewp/forms/shared(.*)$': srcPath('DonationForms/resources/shared$1'),
        '^@givewp/form-builder/registrars$': srcPath('FormBuilder/resources/js/registrars/index.ts'),
        '^@givewp/form-builder(.*)$': srcPath('FormBuilder/resources/js/form-builder/src$1'),
        '^@givewp/components(.*)$': srcPath('Views/Components$1'),
        '^@givewp/css(.*)$': path.resolve(__dirname, 'assets/src/css$1'),
        '^@givewp/promotions(.*)$': srcPath('Promotions/sharedResources$1'),
        '^@givewp/src(.*)$': srcPath('$1'),
        '^@givewp/campaigns(.*)$': srcPath('Campaigns/resources$1'),
        '^@givewp/donors(.*)$': srcPath('Donors/resources$1'),
        '^@givewp/donations(.*)$': srcPath('Donations/resources$1'),
        '^@givewp/subscriptions(.*)$': srcPath('Subscriptions/resources$1'),
        '^@givewp/admin(.*)$': srcPath('Admin$1'),
    },
};
