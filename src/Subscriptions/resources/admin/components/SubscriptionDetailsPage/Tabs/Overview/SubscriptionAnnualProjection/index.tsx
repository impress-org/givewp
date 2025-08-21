import { __ } from '@wordpress/i18n';
import Chart from 'react-apexcharts';
import { amountFormatter } from '@givewp/campaigns/utils';
import { calculateAnnualDonations, Period, chartOptions } from './utils';
import { Header, OverviewPanel } from '@givewp/admin/components';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type SubscriptionAnnualProjectionProps = {
    value: number;
    subscription: Subscription;
    currency: string;
};

/**
 * @unreleased
 */
export default function SubscriptionAnnualProjection({ value, subscription, currency }: SubscriptionAnnualProjectionProps) {
    const currencyFormatter = amountFormatter(currency);

    const period = String(subscription?.period);
    const frequency = Number(subscription?.frequency) || 1;
    const installments = Number(subscription?.installments) || 0;

    // Calculate how many donations occur in a year
    const donationsPerYear = calculateAnnualDonations(period as Period, frequency);

    // For limited subscriptions, use the minimum between installments and donationsPerYear
    const actualDonations = installments === 0 ? donationsPerYear : Math.min(installments, donationsPerYear);

    // Calculate projected annual value/goal
    const projectedAnnualValue = value * actualDonations;

    const progress = Math.ceil((value / projectedAnnualValue) * 100);
    const percentage = Math.min(progress, 100);

    return (
        <OverviewPanel>
            <div className={styles.goalProgressChart}>
                <Header
                    title={__('Projected Annual Value.', 'give')}
                    subtitle={__('Estimated yearly contribution based on billing amount.', 'give')} />
                <div className={styles.chartContainer}>
                    <Chart
                        options={chartOptions(currencyFormatter.format(value))}
                        series={[percentage]}
                        type="radialBar"
                    />
                    <div className={styles.goalDetails}>
                        <span className={styles.detailsLabel}>{__('Estimated contribution', 'give')}</span>
                        <span className={styles.amount}>{currencyFormatter.format(projectedAnnualValue)}</span>
                    </div>
                </div>
            </div>
        </OverviewPanel>
    );
};
