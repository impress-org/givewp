/**
 * External dependencies
 */
const fs = require('fs');
const babelJest = require('babel-jest');

// Remove this workaround when https://github.com/facebook/jest/issues/11444 gets resolved in Jest.
const babelJestInterop = babelJest.__esModule ? babelJest.default : babelJest;

const babelJestTransformer = babelJestInterop.createTransformer({
    presets: ['@wordpress/babel-preset-default'],
});

module.exports = {
    ...babelJestTransformer,
};
