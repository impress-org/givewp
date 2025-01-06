import {__} from '@wordpress/i18n';
import Chart from "react-apexcharts";
import React from "react";

import styles from "./styles.module.scss"

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
})

const GoalProgressChart = ({ value, goal }) => {
    const percentage: number = Math.abs((value / goal) * 100);
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
                            labels: [currency.format(value)],
                        }}
                        series={[percentage]}
                        type="radialBar"
                    />
                </div>
                <div className={styles.goalDetails}>
                    <div className={styles.goal}>{__('Goal')}</div>
                    <div className={styles.amount}>{currency.format(goal)}</div>
                    <div className={styles.goalType}>{__('Amount raised')}</div>
                </div>
            </div>
    )
}

export default GoalProgressChart;
