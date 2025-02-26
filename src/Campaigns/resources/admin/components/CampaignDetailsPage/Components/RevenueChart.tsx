import React, {useEffect, useState} from "react";
import Chart from "react-apexcharts";
import apiFetch from "@wordpress/api-fetch";
import {addQueryArgs} from "@wordpress/url";

const campaignId = new URLSearchParams(window.location.search).get('id');

const RevenueChart = () => {

    const [max, setMax] = useState(0);
    const [categories, setCategories] = useState([]);
    const [series, setSeries] = useState([{name: "Revenue", data: []}]);

    useEffect(() => {
        apiFetch({path: addQueryArgs( '/give-api/v2/campaigns/' + campaignId +'/revenue' ) } )
            .then((data: {date: string, amount: number}[]) => {

                setMax(Math.max(...data.map(item => item.amount)) * 1.1)

                setCategories(data.map(item => item.date))

                setSeries([{
                    name: "Revenue",
                    data: data.map(item => item.amount)
                }])
            });
    }, [])

    const options = {
        chart: {
            id: "campaign-revenue",
            zoom: {
                enabled: false
            },
        },
        xaxis: {
            categories,
            type: 'datetime' as "datetime" | "category" | "numeric",
        },
        yaxis: {
            max,
            min: 0,
        },
        stroke: {
            color: ['#60a1e2'],
            width: 1.5,
            curve: 'smooth' as "straight" | "smooth" | "monotoneCubic" | "stepline" | "linestep" | ("straight" | "smooth" | "monotoneCubic" | "stepline" | "linestep")[],
            lineCap: 'butt' as "butt" | "square" | "round",
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
                            opacity: 1
                        },
                        {
                            offset: 0.6,
                            color: '#b7d4f2',
                            opacity: 50
                        },
                        {
                            offset: 100,
                            color: '#f0f7ff',
                            opacity: 1
                        }
                    ],
                ],
            }
        }
    };

    return (
        <>
            <Chart
                options={options}
                series={series}
                type="area"
                width="100%"
                height="300"
            />
        </>
    )
}

export default RevenueChart
