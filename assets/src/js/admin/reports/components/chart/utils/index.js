export function formatData (data) {

    const formattedLabels = data.labels

    const formattedDatasets = data.datasets.map((dataset) => {
        //const styles = createStyles(props)
        const formatted = {
            label: dataset.label,
            data: dataset.data,
            backgroundColor: ['rgba(105, 184, 104, 0.21)'],
            borderColor: ['rgba(105, 184, 104, 1)'],
            borderWidth: 3
        }
        return formatted
    })

    const formattedData = {
        labels: formattedLabels,
        datasets: formattedDatasets
    }

    return formattedData
}

function createStyles (props) {
    const styles = {
        backgroundColor: ['rgba(105, 184, 104, 0.21)'],
        borderColor: ['rgba(105, 184, 104, 1)'],
        borderWidth: 3
    }

    return styles
}

export function createConfig (type, data) {
    const formattedData = formatData(data)
    const config = {
        type: type,
        data: formattedData,
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 5
                    }
                }]
            }
        }
    }
    return config
}