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
    entry: {
        donationFormBlock: path.resolve(process.cwd(), 'src', 'NextGen/DonationForm/Blocks/DonationFormBlock/block.js'),
        donationFormBlockApp: path.resolve(process.cwd(), 'src', 'NextGen/DonationForm/Blocks/DonationFormBlock/app/DonationFormBlockApp.tsx'),
        nextGenCreditCardGateway: path.resolve(process.cwd(), 'src', 'NextGen/Gateways/Stripe/NextGenCreditCardGateway/nextGenCreditCardGateway.jsx'),
        nextGenTestGateway: path.resolve(process.cwd(), 'src', 'NextGen/Gateways/NextGenTestGateway/nextGenTestGateway.jsx'),
        paymentGatewayRegistrar: path.resolve(process.cwd(), 'src', 'Framework/PaymentGateways/FrontEnd/PaymentGatewayRegistrar/paymentGatewayRegistrar.js'),
    },
};

