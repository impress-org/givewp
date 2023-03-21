import {DonationMethodProps} from '../types';

import styles from '../style.module.scss';

/**
 *
 * @unreleased
 */
export default function DonationMethod({gateway, gatewayId}: DonationMethodProps) {
    return <div className={styles.paymentMethod}>{gateway}</div>;
}
