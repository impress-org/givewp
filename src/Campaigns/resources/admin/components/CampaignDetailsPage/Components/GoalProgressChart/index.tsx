import {__} from '@wordpress/i18n';
import Chart from "react-apexcharts";
import React from "react";

import styles from "./styles.module.scss"
import {getCampaignOptionsWindowData, amountFormatter} from '@givewp/campaigns/utils';

const {currency} = getCampaignOptionsWindowData();
const currencyFormatter = amountFormatter(currency);

type GoalProgressChartProps = {
    value: number;
    goal: number;
}

const GoalProgressChart = ({ value, goal }: GoalProgressChartProps) => {
    const progress = Math.ceil((value / goal) * 100);
    const percentage = Math.min(progress, 100);

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
                                        size: "60%",
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
                                            color: "#4B5563",
                                            fontSize: "12px"
                                        },
                                        value: {
                                            offsetY: -20,
                                            color: "#060C1A",
                                            fontSize: "24px",
                                            show: true
                                        }
                                    }
                                }
                            },
                            colors: ['#459948'],
                            labels: [currencyFormatter.format(value)],
                        }}
                        series={[percentage]}
                        type="radialBar"
                    />
                </div>
                <div className={styles.goalDetails}>
                    <div className={styles.goal}>{__('Goal', 'give')}</div>
                    <div className={styles.amount}>{currencyFormatter.format(goal)}</div>
                    <div className={styles.goalType}>{__('Amount raised', 'give')}</div>
                </div>
            </div>
    )
}

export default GoalProgressChart;
