import StatWidget from '@givewp/admin/components/StatWidget';
import {__} from '@wordpress/i18n';
import {amountFormatter} from '@givewp/admin/utils';
import {getSubscriptionOptionsWindowData} from '@givewp/subscriptions/utils';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type SubscriptionStatsProps = {
    totalContributions: number;
    paymentsCompleted: number;
    totalInstallments: number;
    loading: boolean;
}

/**
 * @unreleased
 */
export default function SubscriptionStats({totalContributions, paymentsCompleted, totalInstallments, loading}: SubscriptionStatsProps) {
    const {currency} = getSubscriptionOptionsWindowData();

    const ongoingInstallments = totalInstallments === 0;

    const paymentProgress = (
        <div className={styles.paymentProgress}>
          {paymentsCompleted} / <span className={styles.installments}>{ongoingInstallments ? '∞' : totalInstallments}</span>
        </div>
      );

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Total contribution so far', 'give')}
                value={totalContributions}
                formatter={amountFormatter(currency)}
                loading={loading}
            />
            <StatWidget
                label={__('Payments completed', 'give')}
                value={paymentProgress}
                formatter={null}
                loading={loading}
            />
        </div>
    );
}