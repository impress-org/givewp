export function formatData (type, data) {

    const formattedLabels = data.labels

    const formattedDatasets = data.datasets.map((dataset) => {
        const styles = createStyles(type, dataset.data)
        const formatted = {
            label: dataset.label,
            data: dataset.data,
            backgroundColor: styles.backgroundColor,
            borderColor: styles.borderColor,
            borderWidth: styles.borderWidth
        }
        return formatted
    })

    const formattedData = {
        labels: formattedLabels,
        datasets: formattedDatasets
    }

    return formattedData
}

function createStyles (type, data) {

    const palette = [
        '#69B868',
        '#F49420',
        '#D75A4B',
        '#556E79',
        '#9EA3A8'
    ]

    const area = ['#69B86844']
    const line = ['#69B868']

    const backgroundColor = type === 'line' ? area : palette
    const borderColor = type === 'line' ? line : palette
    const borderWidth = type === 'line' ? 3 : 0

    const styles = {
        backgroundColor,
        borderColor,
        borderWidth
    }

    return styles
}

export function createConfig (type, data) {
    const formattedData = formatData(type, data)
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