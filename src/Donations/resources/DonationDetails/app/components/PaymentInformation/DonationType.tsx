import {__} from '@wordpress/i18n';
import cx from 'classNames';

import styles from './style.module.scss';
import {DonationTypeProps} from './types';
import SubscriptionBadgeIcon from '@givewp/components/AdminUI/Icons/SubscriptionBadgeIcon';
import SingleBadgeIcon from '@givewp/components/AdminUI/Icons/SingleBadgeIcon';
import RenewalBadgeIcon from '@givewp/components/AdminUI/Icons/RenewalBadgeIcon';

/**
 *
 * @unreleased
 */
function renderPaymentTypeIcon(type: any) {
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
        case 'renewal':
            return (
                <>
                    <div className={cx(styles.typeContainer, styles.renewalContainer)}>
                        <RenewalBadgeIcon />
                    </div>
                    <span>{__('Renewal', 'give')}</span>
                </>
            );
        case 'subscription':
            return (
                <>
                    <div className={cx(styles.typeContainer, styles.subscriptionContainer)}>
                        <SubscriptionBadgeIcon />
                    </div>
                    <span>{__('Subscriber', 'give')}</span>
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
