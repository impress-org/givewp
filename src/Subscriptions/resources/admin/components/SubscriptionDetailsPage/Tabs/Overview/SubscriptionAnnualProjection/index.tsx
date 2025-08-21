import { __ } from '@wordpress/i18n';
import Chart from 'react-apexcharts';
import { amountFormatter } from '@givewp/campaigns/utils';
import { calculateDonationsUntilYearEnd, getCurrentYearCompletedDonations, Period, chartOptions } from './utils';
import { Header, OverviewPanel } from '@givewp/admin/components';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import { Donation } from '@givewp/donations/admin/components/types';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
type SubscriptionAnnualProjectionProps = {
    intendedAmount: number;
    donations: Donation[];
    subscription: Subscription;
    currency: string;
};

/**
 * @unreleased
 */
export default function SubscriptionAnnualProjection({ intendedAmount, subscription, donations, currency }: SubscriptionAnnualProjectionProps) {
    const period = String(subscription?.period);
    const frequency = Number(subscription?.frequency) || 1;
    const installments = Number(subscription?.installments) || 0;
    const subscriptionAmount = Number(intendedAmount) || 0;
    const startDate = subscription?.createdAt ? new Date(subscription.createdAt.date) : new Date();
    const currencyFormatter = amountFormatter(currency);
    const currentYearCompletedDonations = getCurrentYearCompletedDonations(donations);
    const totalContributions = currentYearCompletedDonations?.reduce((acc, donation) => acc + Number(donation.amount.value), 0);

    // Calculate how many donations will occur until the end of the current year with respect to installments
    const remainingDonations = calculateDonationsUntilYearEnd({ period: period as Period, frequency, startDate, installments });

    // Calculate projected annual value based on donations until year end
    const projectedAnnualValue = subscriptionAmount * remainingDonations;

    // Calculate progress based on completed donations vs. projected donations for the year
    const progress = remainingDonations > 0 ? Math.ceil((currentYearCompletedDonations?.length / remainingDonations) * 100) : 0;
    const percentage = Math.min(progress, 100);

    return (
        <OverviewPanel>
            <div className={styles.goalProgressChart}>
                <Header
                    title={__('Projected Annual Value', 'give')}
                    subtitle={__('Estimated yearly contribution based on billing amount.', 'give')} />
                <div className={styles.chartContainer}>
                    <Chart
                        options={chartOptions(currencyFormatter.format(totalContributions))}
                        series={[percentage]}
                        type="radialBar"
                    />
                    <div className={styles.goalDetails}>
                        <span className={styles.detailsLabel}>{__('Estimated contribution', 'give')}</span>
                        <span className={styles.amount}>{currencyFormatter.format(projectedAnnualValue)}</span>
                        <span className={styles.detailsLabel}>{__('Donations until year end', 'give')}</span>
                        <span className={styles.amount}>{remainingDonations}</span>
                    </div>
                </div>
            </div>
        </OverviewPanel>
    );
};
