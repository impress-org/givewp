import { StatWidget } from '@givewp/admin/components';
import { __ } from '@wordpress/i18n';
import { amountFormatter } from '@givewp/admin/utils';
import { Donation } from '@givewp/donations/admin/components/types';
import styles from './styles.module.scss';

/**
 * @since 4.8.0
 */
type SubscriptionStatsProps = {
    donations: Donation[];
    currency: string;
    totalInstallments: number;
    loading: boolean;
}


/**
 * @since 4.8.0
 */
export const getCompletedDonations = (donations: Donation[]) => {
    return donations?.filter(donation => ['publish', 'give_subscription'].includes(donation.status));
};

/**
 * @since 4.8.0
 */
export default function SubscriptionStats({ donations, currency, totalInstallments, loading }: SubscriptionStatsProps) {
    const ongoingInstallments = totalInstallments === 0;
    const completedDonations = getCompletedDonations(donations);
    const paymentsCompleted = completedDonations?.length;
    const totalContributions = completedDonations?.reduce((acc, donation) => acc + Number(donation.amount.value), 0);

    const paymentProgress = (
        <div className={styles.paymentProgress}>
            {paymentsCompleted} / <span>{ongoingInstallments ? '\u221E' : totalInstallments}</span>
        </div>
    );

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Total contribution so far', 'give')}
                value={amountFormatter(currency).format(totalContributions)}
                loading={loading}
            />
            <StatWidget
                label={__('Payments completed', 'give')}
                value={paymentProgress}
                loading={loading}
            />
        </div>
    );
}
