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

    const styles = {
        backgroundColor: palette,
        borderColor: palette,
        borderWidth: 0
    }

    switch (type) {
        case 'line':
            styles.backgroundColor = ['#69B86844']
            styles.borderColor = ['#69B868']
            styles.borderWidth = 3
            break;
        case 'doughnut':
            styles.borderColor = ['#FFFFFF']
            styles.borderWidth = 3
    }

    return styles
}

export function createConfig (type, data) {
    const formattedData = formatData(type, data)
    const config = {
        type: type,
        data: formattedData,
        options: {
            legend: {
                position: 'bottom',
            },
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