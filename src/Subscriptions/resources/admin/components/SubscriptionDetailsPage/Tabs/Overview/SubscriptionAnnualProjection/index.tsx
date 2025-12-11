import { __ } from '@wordpress/i18n';
import Chart from 'react-apexcharts';
import { amountFormatter } from '@givewp/campaigns/utils';
import { Header, OverviewPanel } from '@givewp/admin/components';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import { Donation } from '@givewp/donations/admin/components/types';
import {
    getCurrentYearCompletedDonations,
    calculateTotalContributions,
    calculateProgressPercentage,
    chartOptions
} from './utils';
import styles from './styles.module.scss';

/**
 * @since 4.8.0
 */
type SubscriptionAnnualProjectionProps = {
    donations: Donation[];
    subscription: Subscription;
    currency: string;
};

/**
 * @since 4.8.0
 */
export default function SubscriptionAnnualProjection({ subscription, donations, currency }: SubscriptionAnnualProjectionProps) {
    const currencyFormatter = amountFormatter(currency);
    const projectedAnnualRevenue = subscription?.projectedAnnualRevenue;

    // Calculate completed donations and contributions using utility functions
    const currentYearCompletedDonations = getCurrentYearCompletedDonations(donations);
    const totalContributions = calculateTotalContributions(currentYearCompletedDonations);

    // Calculate progress - ensure both values are in the same units
    const projectedAmount = projectedAnnualRevenue ? Number(projectedAnnualRevenue?.value) : 0;
    const percentage = projectedAmount > 0 ? calculateProgressPercentage(totalContributions, projectedAmount) : 0;

    // Set chart options and series
    const options = chartOptions(currencyFormatter.format(totalContributions));
    const series = [percentage];


    return (
        <OverviewPanel>
            <div className={styles.goalProgressChart}>
                <Header
                    title={__('Projected Annual Revenue', 'give')}
                    subtitle={__('Estimated yearly contribution based on billing amount.', 'give')} />
                <div className={styles.chartContainer}>
                    <Chart
                        options={options}
                        series={series}
                        type="radialBar"
                    />
                    <div className={styles.goalDetails}>
                        <span className={styles.detailsLabel}>{__('Estimated contribution', 'give')}</span>
                        <span className={styles.amount}>
                            {currencyFormatter.format(projectedAnnualRevenue?.value)}
                        </span>
                    </div>
                </div>
            </div>
        </OverviewPanel>
    );
}

