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

export function getHighlightValue( data ) {
	const highlightValue = data.datasets[ 0 ].highlight;
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
				// Disable the on-canvas tooltip
				enabled: false,

				custom: function( tooltipModel ) {
					// Tooltip Element
					let tooltipEl = document.getElementById( 'givewp-mini-chartjs-tooltip' );

					// Create element on first render
					if ( ! tooltipEl ) {
						tooltipEl = document.createElement( 'div' );
						tooltipEl.id = 'givewp-mini-chartjs-tooltip';
						tooltipEl.innerHTML = '<div class="givewp-mini-tooltip-header">$1200</div><div class="givewp-mini-tooltip-body"><bold>12 Donors</b><br>June 2019</div>';
						document.body.appendChild( tooltipEl );
					}

					// Hide if no tooltip
					if ( tooltipModel.opacity === 0 ) {
						tooltipEl.style.opacity = 0;
						return;
					}

					// Set caret Position
					tooltipEl.classList.remove( 'above', 'below', 'no-transform' );
					if ( tooltipModel.yAlign ) {
						tooltipEl.classList.add( tooltipModel.yAlign );
					} else {
						tooltipEl.classList.add( 'no-transform' );
					}

					// `this` will be the overall tooltip
					const position = this._chart.canvas.getBoundingClientRect();

					// Display, position, and set styles for font
					tooltipEl.style.opacity = 1;
					tooltipEl.style.position = 'absolute';
					tooltipEl.style.left = position.left + window.pageXOffset + tooltipModel.caretX - ( tooltipEl.offsetWidth / 2 ) + 'px';
					tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.caretY - ( tooltipEl.offsetHeight + 12 ) + 'px';
					tooltipEl.style.pointerEvents = 'none';
				},
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
