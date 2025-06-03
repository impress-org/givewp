import React, {useEffect, useState} from 'react';
import Chart from 'react-apexcharts';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

export interface TimeSeriesChartProps {
    /** The endpoint to fetch time series data from */
    endpoint: string;
    /** Optional query parameters to add to the endpoint */
    queryParams?: Record<string, string>;
    /** Optional value formatter function */
    valueFormatter?: (value: number) => string;
    /** Optional chart height */
    height?: string | number;
    /** Optional chart width */
    width?: string | number;
    /** Optional chart title */
    title?: string;
    /** Optional chart colors */
    colors?: string[];
    /** Optional gradient colors for the area fill */
    gradientColors?: Array<{offset: number; color: string; opacity: number}>;
}

const getDefaultData = (days = 7) => {
    const data = [];
    for (let i = 0; i < days; i++) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        data.push({x: date.toISOString().split('T')[0], y: 0});
    }
    return data;
};

const TimeSeriesChart: React.FC<TimeSeriesChartProps> = ({
    endpoint,
    queryParams = {},
    valueFormatter = (value) => value.toString(),
    height = '100%',
    width = '100%',
    title = '',
    colors = ['#60a1e2'],
    gradientColors = [
        {offset: 0, color: '#eee', opacity: 1},
        {offset: 0.6, color: '#b7d4f2', opacity: 50},
        {offset: 100, color: '#f0f7ff', opacity: 1},
    ],
}) => {
    const [max, setMax] = useState(100);
    const [series, setSeries] = useState([{name: title, data: getDefaultData()}]);

    useEffect(() => {
        apiFetch({path: addQueryArgs(endpoint, queryParams)}).then(
            (data: {date: string; amount: number}[]) => {
                if (data?.length > 0) {
                    setMax(undefined);
                    setSeries([
                        {
                            name: title,
                            data: data.map((item) => ({
                                x: item.date,
                                y: item.amount,
                            })),
                        },
                    ]);
                }
            }
        );
    }, [endpoint, queryParams, title]);

    const options = {
        chart: {
            id: 'time-series-chart',
            zoom: {
                enabled: false,
            },
            toolbar: {
                show: false,
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
            color: colors,
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
                colorStops: [
                    gradientColors.map(({offset, color, opacity}) => ({
                        offset,
                        color,
                        opacity,
                    })),
                ],
            },
        },
    };

    return <Chart options={options} series={series} type="area" width={width} height={height} />;
};

export default TimeSeriesChart; 