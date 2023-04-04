import DonationType from '../DonationType';
import StatusSelector from '@givewp/components/AdminUI/StatusSelector';

import {donationStatusOptions} from '../../../config/donationStatus';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export type LegendProps = {
    title: string;
    donationType: string;
};

export default function Legend({title, donationType}: LegendProps) {
    return (
        <div className={styles.legend}>
            <legend>
                <h2>{title}</h2>
            </legend>
            <div className={styles.paymentType}>
                <DonationType donationType={donationType} />
                <StatusSelector options={donationStatusOptions} />
            </div>
        </div>
    );
}
