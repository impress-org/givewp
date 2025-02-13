const path = require('path');

/**
 * This is a custom webpack configuration, intended to be consumed by Laravel Mix specifically.
 * This is important so the IDE can understand the aliases and resolve paths correctly.
 *
 * Note: @wordpress/scripts is set up to use its own webpack configuration.
 */
module.exports = {
    resolve: {
        alias: {
            '@givewp/components': path.resolve(__dirname, 'src/Views/Components/'),
            '@givewp/css': path.resolve(__dirname, 'assets/src/css/'),
            '@givewp/promotions': path.resolve(__dirname, 'src/Promotions/sharedResources/'),
            '@givewp/src': path.resolve(__dirname, 'src/'),
        },
    },
};
