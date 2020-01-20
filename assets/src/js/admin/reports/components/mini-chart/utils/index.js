export function formatData( data ) {
	const formattedLabels = data.labels;

	const formattedDatasets = data.datasets.map( ( dataset ) => {
		const formatted = {
			label: dataset.label,
			data: dataset.data,
			backgroundColor: '#FFFFFF',
			borderColor: '#DDDDDD',
		};
		return formatted;
	} );

	const formattedData = {
		labels: formattedLabels,
		datasets: formattedDatasets,
	};

	return formattedData;
}

export function getTrend( data ) {
	const trend = data.datasets[ 0 ].trend;
	return trend;
}

export function getHighlightValue( highlight, data ) {
	const highlightValue = data.datasets[ 0 ].highlights[ highlight ];
	return highlightValue;
}

export function createConfig( data ) {
	const formattedData = formatData( data );
	const config = {
		type: 'line',
		data: formattedData,
		options: {
			layout: {
				padding: 4,
			},
			legend: {
				display: false,
			},
			scales: {
				yAxes: [ {
					display: false,
				} ],
				xAxes: [ {
					display: false,
				} ],
			},
			tooltips: {
				enabled: false,
			},
			elements: {
				point: {
					radius: 0,
				},
			},
		},
	};
	return config;
}
