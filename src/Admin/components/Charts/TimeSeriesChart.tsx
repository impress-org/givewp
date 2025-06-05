import React, {useEffect, useState} from 'react';
import Chart from 'react-apexcharts';
import {ApexOptions} from 'apexcharts';
import apiFetch from '@wordpress/api-fetch';

/**
 * @unreleased
 */
type TimeSeriesChartProps = {
    endpoint: string;
    amountFormatter: Intl.NumberFormat;
    title?: string;
};

/**
 * @unreleased
 */
type Donation = {
    createdAt: {
        date: string;
    };
    amount: {
        value: string;
    };
};

/**
 * @unreleased
 */
type DataPoint = {
    x: string;
    y: number;
};

/**
 * @unreleased
 */
const normalizeData = (donations: Donation[]): DataPoint[] => {
    const map = new Map<string, number>();

    // Group donations by date with total amounts - fill missing dates with 0.
    donations.forEach((donation) => {
        const date = donation.createdAt.date.split(' ')[0];
        const amount = parseFloat(donation.amount.value);
        map.set(date, (map.get(date) || 0) + amount);
    });

    // Convert to sorted array of data points.
    return Array.from(map.entries())
        .map(([date, amount]) => ({
            x: date,
            y: amount,
        }))
        .sort((a, b) => a.x.localeCompare(b.x));
};

/**
 * @unreleased
 */
export default function TimeSeriesChart({endpoint, amountFormatter, title = ''}: TimeSeriesChartProps) {
    const [series, setSeries] = useState([{name: title, data: []}]);

    useEffect(() => {
        apiFetch<Donation[]>({path: endpoint}).then((data) => {
            const normalized = normalizeData(data);
            setSeries([{name: title, data: normalized}]);
        });
    }, [endpoint]);

    const options: ApexOptions = {
        chart: {
            type: 'area' as const,
            toolbar: {show: false},
            zoom: {enabled: false},
        },
        xaxis: {
            type: 'datetime',
            labels: {format: 'MMM dd'},
        },
        yaxis: {
            min: 0,
            labels: {
                formatter: (val) => amountFormatter.format(val),
            },
        },
        dataLabels: {enabled: false},
        stroke: {
            curve: 'smooth',
            width: 2,
            colors: ['#60a1e2'],
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.3,
                opacityTo: 0,
                stops: [0, 100],
            },
        },
        tooltip: {
            x: {
                format: 'MMM dd',
            },
        },
    };

    return <Chart options={options} series={series} type="area" height="250" />;
}
