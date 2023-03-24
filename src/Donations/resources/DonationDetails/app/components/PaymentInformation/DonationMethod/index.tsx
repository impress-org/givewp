import styles from './style.module.scss';
import {DonationMethodProps} from './types';
import {paymentMethodList} from './paymentMethodList';

/**
 *
 * @unreleased
 */

function GatewayLogo(gatewwayId: string) {
    for (const prop in paymentMethodList) {
        if (prop === gatewwayId) {
            const matchingValue = paymentMethodList[prop];
            return matchingValue;
        }
    }
}

export default function DonationMethod({gatewayLabel, gatewayId}: DonationMethodProps) {
    return (
        <div className={styles.paymentMethod}>
            <span>{GatewayLogo(gatewayId)}</span>
            <span>{gatewayLabel}</span>
        </div>
    );
}
