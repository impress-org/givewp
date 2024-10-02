import React from "react";
import Chart from "react-apexcharts";

const CampaignRevenueChart = ({ campaign }) => {

    const options = {
        chart: {
            id: "campaign-revenue",
            zoom: {
                enabled: false
            },
        },
        xaxis: {
            categories: ['Aug 06', 'Aug 07', 'Aug 08', 'Aug 09']
        },
        yaxis: {
            max: 200,
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

    const series = [
        {
            name: "Revenue",
            data: [0, 100, 50, 150]
        }
    ];

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

export default CampaignRevenueChart
