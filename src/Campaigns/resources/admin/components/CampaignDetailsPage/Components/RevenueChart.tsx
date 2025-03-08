import React, {useEffect, useState} from 'react';
import Chart from 'react-apexcharts';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import {amountFormatter, getCampaignOptionsWindowData} from '@givewp/campaigns/utils';

const campaignId = new URLSearchParams(window.location.search).get('id');

const getDefaultData = (days = 7) => {
    const data = [];

    for (let i = 0; i < days; i++) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        data.push({x: date, y: 0});
    }

    return data;
};

const RevenueChart = () => {
    const {currency} = getCampaignOptionsWindowData();
    const currencyFormatter = amountFormatter(currency);
    const [max, setMax] = useState(100);
    const [series, setSeries] = useState([{name: 'Revenue', data: getDefaultData()}]);

    useEffect(() => {
        apiFetch({path: addQueryArgs('/give-api/v2/campaigns/' + campaignId + '/revenue')}).then(
            (data: {date: string; amount: number}[]) => {
                if (data?.length > 0) {
                    setMax(Math.max(...data.map((item) => item.amount)) * 1.2);

                    setSeries([
                        {
                            name: 'Revenue',
                            data: data.map((item) => {
                                return {
                                    x: item.date,
                                    y: item.amount,
                                };
                            }),
                        },
                    ]);
                }
            }
        );
    }, []);

    const options = {
        chart: {
            id: 'campaign-revenue',
            zoom: {
                enabled: false,
            },
        },
        xaxis: {
            type: 'datetime' as 'datetime' | 'category' | 'numeric',
        },
        yaxis: {
            max,
            min: 0,
            tickAmount: 6,
            labels: {
                formatter: (value) => {
                    return currencyFormatter.format(Math.ceil(Number(value)));
                },
            },
        },
        stroke: {
            color: ['#60a1e2'],
            width: 1.5,
            curve: 'smooth' as
                | 'straight'
                | 'smooth'
                | 'monotoneCubic'
                | 'stepline'
                | 'linestep'
                | ('straight' | 'smooth' | 'monotoneCubic' | 'stepline' | 'linestep')[],
            lineCap: 'butt' as 'butt' | 'square' | 'round',
        },
        dataLabels: {
            enabled: false,
        },
        fill: {
            type: 'gradient',
            gradient: {
                colorStops: [
                    [
                        {
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
                        },
                    ],
                ],
            },
        },
    };

    return <Chart options={options} series={series} type="area" width="100%" height="100%" />;
};

export default RevenueChart;
