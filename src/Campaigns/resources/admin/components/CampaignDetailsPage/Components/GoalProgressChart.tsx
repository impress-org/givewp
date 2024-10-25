import {__} from '@wordpress/i18n';
import Chart from "react-apexcharts";
import React from "react";

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
})

const GoalProgressChart = ({ value, goal }) => {
    const percentage: number = Math.abs((value / goal) * 100);
    return (
        <>
            <div style={{
                display: 'flex',
                gap: '20px',
                alignItems: 'center',
            }}>
                <div style={{
                    /**
                     * The size of the chart is relative to the container.
                     * To get close to the design,
                     *  the size is balances at flex 3/2
                     *  and the margins use negative values to control padding
                     */
                    flex: 3,
                    margin: '0 -50px',
                }}>
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
                <div style={{ flex: 2 }}>
                    <div style={{
                        fontSize: '14px',
                        fontWeight: 400,
                        lineHeight: '20px',
                    }}>
                        {__('Goal')}
                    </div>
                    <div style={{
                        color: '#2D802F',
                        fontSize: '18px',
                        fontWeight: 600,
                        lineHeight: '20px',
                    }}>{currency.format(goal)}</div>
                    <div style={{
                        fontSize: '12px',
                        fontWeight: 400,
                        lineHeight: '18px',
                    }}>{__('Amount raised')}</div>
                </div>
            </div>
        </>
    )
}

export default GoalProgressChart;
