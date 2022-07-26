import field from './field';
import company from './company';
import donorName from './donor-name';
import email from './email';
import paymentGateways from './payment-gateways';

/**
 * @note Blocks in the appender are listed in the order that the blocks are registered.
 */

const fieldBlocks = [
    field,
    company,
    donorName,
    email,
    paymentGateways,
];

const fieldBlockNames = fieldBlocks.map(field => field.name);

export default fieldBlocks;
export {
    fieldBlockNames,
};
