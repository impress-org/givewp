import {StatWidget} from '@givewp/admin/components';
import {__} from '@wordpress/i18n';
import {amountFormatter} from '@givewp/admin/utils';
import { Donation } from '@givewp/donations/admin/components/types';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type SubscriptionStatsProps = {
    donations: Donation[];
    currency: string;
    totalInstallments: number;
    loading: boolean;
}

/**
 * @unreleased
 */
export default function SubscriptionStats({donations, currency, totalInstallments, loading}: SubscriptionStatsProps) {
    const ongoingInstallments = totalInstallments === 0;
    const paymentsCompleted = donations?.length;
    const totalContributions = donations?.reduce((acc, donation) => acc + Number(donation.amount.value), 0);

    const paymentProgress = (
        <div className={styles.paymentProgress}>
          {paymentsCompleted} / <span>{ongoingInstallments ? '\u221E'	 : totalInstallments}</span>
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
