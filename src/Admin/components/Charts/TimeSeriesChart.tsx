import React, {useEffect, useState} from 'react';
import Chart from 'react-apexcharts';
import {ApexOptions} from 'apexcharts';
import apiFetch from '@wordpress/api-fetch';

/**
 * @since 4.4.0
 */
type TimeSeriesChartProps = {
    endpoint: string;
    amountFormatter: Intl.NumberFormat;
    title?: string;
};

/**
 * @since 4.4.0
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
 * @since 4.4.0
 */
type DataPoint = {
    x: string;
    y: number;
};

/**
 * @since 4.4.0
 * Generates a range of dates around a target date for displaying a single donation graph.
 */
const getCenteredGraphRange = (targetDate: string) => {
    const result = [];
    const date = new Date(targetDate);

    for (let i = -3; i <= 3; i++) {
        const currentDate = new Date(date);
        currentDate.setDate(date.getDate() + i);
        result.push(currentDate.toISOString().split('T')[0]);
    }
    return result;
};

/**
 * @since 4.4.0
 */
const normalizeData = (donations: Donation[]): DataPoint[] => {
    const map = new Map<string, number>();

    // Group donations by date with sum amounts - fill missing dates with 0.
    donations.forEach((donation) => {
        const date = donation.createdAt.date.split(' ')[0];
        const amount = parseFloat(donation.amount.value);
        map.set(date, (map.get(date) || 0) + amount);
    });

    // Set graph range & points for single donations.
    if (map.size === 1) {
        const donationDate = donations[0]?.createdAt.date.split(' ')[0];
        const graphRange = getCenteredGraphRange(donationDate);

        return graphRange.map((date) => ({
            x: date,
            y: map.get(date) || 0,
        }));
    }

    // Convert to sorted array of data points.
    return Array.from(map.entries())
        .map(([date, amount]) => ({
            x: date,
            y: amount,
        }))
        .sort((a, b) => a.x.localeCompare(b.x));
};

/**
 * @since 4.4.0
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
            labels: {format: 'MMM dd, yyyy'},
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
                format: 'MMM dd, yyyy',
            },
        },
    };

    return <Chart options={options} series={series} type="area" height="250" />;
}
