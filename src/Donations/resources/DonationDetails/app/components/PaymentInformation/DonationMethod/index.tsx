import styles from './style.module.scss';
import {DonationMethodProps} from './types';

/**
 *
 * @unreleased
 */

export default function DonationMethod({gatewayLabel, gatewayId}: DonationMethodProps) {
    return <div className={styles.paymentMethod}>{gatewayLabel}</div>;
}
