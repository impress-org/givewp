// Format data from Reports API for ChartJS
export function formatData (type, data) {

    const formattedLabels = data.labels.slice(0)

    const formattedDatasets = data.datasets.map((dataset, index) => {

        // Setup styles
        const styles = createStyles(type, dataset.data, index)

        const formatted = {
            label: (' ' + dataset.label).slice(1),
			data: dataset.data.slice(0),
			yAxisID: `y-axis-${index}`,
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

// Create chart styles from predifined pallette,
// depending on chart type
function createStyles (type, data, index) {

    const palette = [
		'#69B868',
		'#556E79',
		'#9EA3A8',
        '#D75A4B',
		'#F49420'
    ]

    const styles = {
        backgroundColor: palette,
        borderColor: palette,
        borderWidth: 0
    }

    // Handle special styles needed for 'line' and 'doughnut' charts
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

// Return config object for ChartJS
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

    // Setup yAxes to begin at zero if chart is 'line' or 'bar'
    if (type === 'line' || type === 'bar') {

		const yAxes = data.datasets.map((dataset, index) => {
			return {
				id: `y-axis-${index}`,
				ticks: {
					beginAtZero: true,
				}
			}
		})

        config.options.scales = {
            yAxes: yAxes
        }
    }

    return config
}
