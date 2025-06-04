import React, {useEffect, useState} from 'react';
import Chart from 'react-apexcharts';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

/**
 * @unreleased
 */
interface DonationAmount {
    value: string;
    valueInMinorUnits: string;
    currency: string;
}

/**
 * @unreleased
 */
interface DonationDate {
    date: string;
    timezone_type: number;
    timezone: string;
}

/**
 * @unreleased
 */
export interface DonationData {
    id: number;
    amount: DonationAmount;
    createdAt: DonationDate;
}

/**
 * @unreleased
 */
export interface TimeSeriesChartProps {
    endpoint?: string;
    data?: DonationData[];
    queryParams?: Record<string, string>;
    valueFormatter?: (value: number) => string;
    height?: string | number;
    width?: string | number;
    title?: string;
    colors?: string[];
    gradientColors?: Array<{offset: number; color: string; opacity: number}>;
}

/**
 * @unreleased
 */
const getDefaultData = (days = 7) => {
    const data = [];

    for (let i = 0; i < days; i++) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        data.push({x: date.toISOString().split('T')[0], y: 0});
    }

    return data;
};

/**
 * @unreleased
 */
const TimeSeriesChart: React.FC<TimeSeriesChartProps> = ({
    endpoint,
    data,
    queryParams = {},
    valueFormatter = (value) => value.toString(),
    height = '100%',
    width = '100%',
    title = '',
    colors = ['#60a1e2'],
    gradientColors = [
        [{
            offset: 0,
            color: '#eee',
            opacity: 1,
        },
        {
            offset: 0.6,
            color: '#b7d4f2',
            opacity: 50,
        },
        {
            offset: 100,
            color: '#f0f7ff',
            opacity: 1,
        }],
    ],
}) => {
    const [max, setMax] = useState(100);
    const [series, setSeries] = useState([{name: title, data: getDefaultData()}]);

    useEffect(() => {
        if (data?.length > 0) {
            setMax(undefined);
            setSeries([
                {
                    name: title,
                    data: data.map((item) => ({
                        x: item.createdAt.date.split(' ')[0],
                        y: parseFloat(item.amount.value),
                    })),
                },
            ]);
            return;
        }

        if (endpoint) {
            apiFetch({path: addQueryArgs(endpoint, queryParams)}).then(
                (responseData: DonationData[]) => {
                    if (responseData?.length > 0) {
                        setMax(undefined);
                        setSeries([
                            {
                                name: title,
                                data: responseData.map((item) => ({
                                    x: item.createdAt.date.split(' ')[0],
                                    y: parseFloat(item.amount.value),
                                })),
                            },
                        ]);
                    }
                }
            );
        }
    }, [endpoint, queryParams, title, data]);

    const options = {
        chart: {
            id: 'time-series-chart',
            zoom: {
                enabled: false,
            },
            toolbar: {
                show: false,
            },
            parentHeightOffset: 0,
            height: '100%',
            width: '100%',
            animations: {
                enabled: true
            },
        },
        xaxis: {
            type: 'datetime' as const,
        },
        yaxis: {
            min: 0,
            max,
            showForNullSeries: false,
            labels: {
                formatter: (value) => valueFormatter(Number(value)),
            },
        },
        stroke: {
            color: ['#60a1e2'],
            width: 1.5,
            curve: 'smooth' as const,
            lineCap: 'butt' as const,
        },
        dataLabels: {
            enabled: false,
        },
        fill: {
            type: 'gradient',
            gradient: {
                colorStops: gradientColors,
            },
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: '100%'
                }
            }
        }]
    };

    const containerStyle = {
        position: 'relative' as const,
        height: height,
        width: width,
        maxWidth: '100%'
    };

    return (
        <div style={containerStyle}>
            <Chart options={options} series={series} type="area" width="100%" height="100%" />
        </div>
    );
};

export default TimeSeriesChart;
