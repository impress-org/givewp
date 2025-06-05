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
        date: string; // Format: YYYY-MM-DD HH:mm:ss
    };
    amount: {
        value: string; // Decimal string
    };
};

/**
 * @unreleased
 */
type DataPoint = {
    x: string; // Format: YYYY-MM-DD
    y: number;
};

/**
 * @unreleased
 */
const getLast7Days = () => {
    const result = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        result.push(date.toISOString().split('T')[0]); // Format: YYYY-MM-DD
    }
    return result;
};

/**
 * @unreleased
 */
const normalizeData = (donations: Donation[], last7Days: string[]): DataPoint[] => {
    const map = new Map<string, number>();

    donations.forEach((donation) => {
        const date = donation.createdAt.date.split(' ')[0];
        const amount = parseFloat(donation.amount.value);
        map.set(date, (map.get(date) || 0) + amount);
    });

    return last7Days.map((date) => ({
        x: date,
        y: map.get(date) || 0,
    }));
};

/**
 * @unreleased
 */
export default function TimeSeriesChart({endpoint, amountFormatter, title = ''}: TimeSeriesChartProps) {
    const [series, setSeries] = useState([{name: title, data: []}]);

    useEffect(() => {
        const last7Days = getLast7Days();

        apiFetch<Donation[]>({path: endpoint}).then((data) => {
            const normalized = normalizeData(data, last7Days);
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
