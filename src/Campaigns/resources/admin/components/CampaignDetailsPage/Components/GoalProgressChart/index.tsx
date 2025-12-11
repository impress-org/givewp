import {__} from '@wordpress/i18n';
import Chart from 'react-apexcharts';
import React from 'react';

import styles from './styles.module.scss';
import {amountFormatter, getCampaignOptionsWindowData} from '@givewp/campaigns/utils';
import type {Campaign} from '@givewp/campaigns/admin/components/types';

const {currency} = getCampaignOptionsWindowData();
const currencyFormatter = amountFormatter(currency);

type GoalProgressChartProps = {
    value: number;
    goal: number;
    goalType: Partial<Campaign>['goalType'];
};

const goalTypeLabels = {
    amount: __('Amount raised', 'give'),
    donations: __('Number of donations', 'give'),
    donors: __('Number of donors', 'give'),
    amountFromSubscriptions: __('Recurring amount raised', 'give'),
    subscriptions: __('Number of recurring donations', 'give'),
    donorsFromSubscriptions: __('Number of recurring donors', 'give'),
};

const GoalProgressChart = ({value, goal, goalType}: GoalProgressChartProps) => {
    const progress = Math.ceil((value / goal) * 100);
    const percentage = Math.min(progress, 100);

    const isCurrencyGoal = ['amount', 'amountFromSubscriptions'].includes(goalType);
    const formattedValue = isCurrencyGoal ? currencyFormatter.format(value) : value.toString();
    const formattedGoal = isCurrencyGoal ? currencyFormatter.format(goal) : goal.toString();

    return (
        <div className={styles.goalProgressChart}>
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
            </div>
            <div className={styles.goalDetails}>
                <div className={styles.goal}>{__('Goal', 'give')}</div>
                <div className={styles.amount}>{formattedGoal}</div>
                <div className={styles.goalType}>{goalTypeLabels[goalType]}</div>
            </div>
        </div>
    );
};

export default GoalProgressChart;
