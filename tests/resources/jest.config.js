module.exports = {
    rootDir: '../../',
    preset: '@wordpress/jest-preset-default',
    moduleNameMapper: {
        '^@givewp/form-builder(.*)$': '<rootDir>/src/FormBuilder/resources/js/form-builder/src$1',
        '^@givewp/forms/app(.*)$': '<rootDir>/src/DonationForms/resources/app$1',
    },
    setupFilesAfterEnv: ['<rootDir>/tests/resources/config/testing-library.js'],
    testEnvironmentOptions: {
        url: 'http://localhost/',
    },
    testPathIgnorePatterns: [
        '/.git/',
        '/node_modules/',
        '/vendor/',
        '<rootDir>/.*/assets/dist/',
        '<rootDir>/.*/build/',
        '<rootDir>/.+.d.ts$',
    ],
    resolver: '<rootDir>/tests/resources/scripts/resolver.js',
    transform: {
        '^.+\\.[jt]sx?$': '<rootDir>/tests/resources/scripts/babel-transformer.js',
    },
    transformIgnorePatterns: ['/node_modules/(?!(docker-compose|yaml)/)', '\\.pnp\\.[^\\/]+$'],
    watchPlugins: ['jest-watch-typeahead/filename', 'jest-watch-typeahead/testname'],
    coverageDirectory: '<rootDir>/tests/resources/coverage/',
    reporters: ['default'],
};
