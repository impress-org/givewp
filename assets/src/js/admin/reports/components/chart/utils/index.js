export function formatData (type, data) {

    const formattedLabels = data.labels

    const formattedDatasets = data.datasets.map((dataset, index) => {
        const styles = createStyles(type, dataset.data, index)
        const formatted = {
            label: (' ' + dataset.label).slice(1),
            data: dataset.data.slice(0),
            backgroundColor: styles.backgroundColor,
            borderColor: styles.borderColor,
            borderWidth: styles.borderWidth
        }
        return formatted
    })

    const formattedData = {
        labels: formattedLabels,
        datasets: formattedDatasets,
    }

    return formattedData
}

function createStyles (type, data, index) {

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
            styles.backgroundColor = [
                palette[index] + '44'
            ]
            styles.borderColor = [
                palette[index]
            ]
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
    let config = {
        type: type,
        data: formattedData,
        options: {
            legend: {
                display: false
            },
            layout: {
                padding: 16
            }
        }
    }

    if (type === 'line' || type === 'bar') {
        config.options.scales = {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                }
            }]
        }
    }

    return config
}