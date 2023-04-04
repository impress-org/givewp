import {__} from '@wordpress/i18n';
import cx from 'classNames';

import SubscriptionBadgeIcon from '@givewp/components/AdminUI/Icons/SubscriptionBadgeIcon';
import SingleBadgeIcon from '@givewp/components/AdminUI/Icons/SingleBadgeIcon';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export type DonationTypeProps = {
    donationType: 'single' | 'subscription' | string | null;
};

function renderPaymentTypeIcon(type: 'single' | 'subscription' | string | null) {
    switch (type) {
        case 'single':
            return (
                <>
                    <div className={cx(styles.typeContainer, styles.singleContainer)}>
                        <SingleBadgeIcon />
                    </div>
                    <span>{__('One-Time', 'give')}</span>
                </>
            );
        case 'subscription':
            return (
                <>
                    <div className={cx(styles.typeContainer, styles.subscriptionContainer)}>
                        <SubscriptionBadgeIcon />
                    </div>
                    <span>{__('Recurring', 'give')}</span>
                </>
            );
        default:
            return '';
    }
}

/**
 *
 * @unreleased
 */
export default function DonationType({donationType}: DonationTypeProps) {
    return <div className={styles.donationBadge}>{renderPaymentTypeIcon(donationType)}</div>;
}
