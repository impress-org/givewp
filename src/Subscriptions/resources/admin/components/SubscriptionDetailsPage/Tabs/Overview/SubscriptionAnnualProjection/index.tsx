import { __ } from '@wordpress/i18n';
import Chart from 'react-apexcharts';

import styles from './styles.module.scss';
import { amountFormatter } from '@givewp/campaigns/utils';
import { Header, OverviewPanel } from '@givewp/admin/components';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import { getSubscriptionOptionsWindowData } from '@givewp/subscriptions/utils';

const { currency } = getSubscriptionOptionsWindowData();
const currencyFormatter = amountFormatter(currency);

/**
 * @unreleased
 * @param frequency
 * @returns
 */
type SubscriptionProjectionChartProps = {
    value: number;
    subscription: Subscription;
};

/**
 * @unreleased
 */
const getNumericPeriodValue = (period: string) => {
    switch (period) {
        case 'day':
            return 365;
        case 'week':
            return 52;
        case 'month':
            return 12;
        case 'year':
            return 1;
    }
};

/**
 * Calculate the number of donations in a year based on period and frequency
 * @unreleased
 */
const calculateAnnualDonations = (period: string, frequency: number) => {
    const periodsInYear = getNumericPeriodValue(period);
    return Math.floor(periodsInYear / frequency);
};

/**
 * @unreleased
 */
export default function SubscriptionAnnualProjection({ value, subscription }: SubscriptionProjectionChartProps) {
    const period = String(subscription?.period);
    const frequency = Number(subscription?.frequency) || 1;
    const installments = Number(subscription?.installments) || 0;

    // Calculate how many donations occur in a year
    const donationsPerYear = calculateAnnualDonations(period, frequency);

    // For limited subscriptions, use the minimum between installments and donationsPerYear
    const actualDonations = installments === 0 ? donationsPerYear : Math.min(installments, donationsPerYear);

    // Calculate projected annual value/goal
    const projectedAnnualValue = value * actualDonations;

    const progress = Math.ceil((value / projectedAnnualValue) * 100);
    const percentage = Math.min(progress, 100);
    const formattedValue = currencyFormatter.format(value);

    return (
        <OverviewPanel>
            <div className={styles.goalProgressChart}>
                <Header
                    title={__('Projected Annual Value.', 'give')}
                    subtitle={__('Estimated yearly contribution based on billing amount.', 'give')} />
                <div className={styles.chartContainer}>
                    <Chart
                        options={{
                            chart: {
                                height: 1024,
                                type: 'radialBar',
                            },
                            plotOptions: {
                                radialBar: {
                                    hollow: {
                                        margin: 15,
                                        size: '60%',
                                    },
                                    dataLabels: {
                                        /**
                                         * The "name" is the top label, here it is the value/amount
                                         * The "value" is the percent progress
                                         *
                                         * Note: These are visually inverted (using offsetY) to match the design
                                         */
                                        name: {
                                            offsetY: 20,
                                            show: true,
                                            color: '#4B5563',
                                            fontSize: '12px',
                                        },
                                        value: {
                                            offsetY: -20,
                                            color: '#060C1A',
                                            fontSize: '24px',
                                            show: true,
                                        },
                                    },
                                },
                            },
                            colors: ['#459948'],
                            labels: [formattedValue],
                        }}
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
