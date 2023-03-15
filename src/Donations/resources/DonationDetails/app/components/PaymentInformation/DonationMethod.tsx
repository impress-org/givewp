import PaypalIcon from '@givewp/components/AdminUI/Icons/PaypalIcon';

import {DonationMethodProps} from './types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
function renderGatewayIcon(gatewayId) {
    switch (gatewayId) {
        case 'paypal':
            return <PaypalIcon />;
        case 'stripe':
            return <span>stripe test</span>;
        case 'test-gateway-next-gen':
            return <span>next-gen test</span>;
        case 'offline-donation':
            return <span>offline-donation test</span>;
        default:
            return '';
    }
}

/**
 *
 * @unreleased
 */
export default function DonationMethod({gateway, gatewayId}: DonationMethodProps) {
    return (
        <div className={styles.paymentMethod}>
            {renderGatewayIcon(gatewayId)}
            {gateway}
        </div>
    );
}
