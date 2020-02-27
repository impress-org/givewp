export function formatData( data ) {
	const formattedLabels = data.labels;

	const formattedDatasets = data.datasets.map( ( dataset ) => {
		const formatted = {
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

export function getTooltipText( data ) {
	const tooltipText = data.datasets[ 0 ].info;
	return tooltipText;
}

export function createConfig( data ) {
	const formattedData = formatData( data );
	const config = {
		type: 'line',
		data: formattedData,
		options: {
			hover: {
				intersect: false,
			},
			plugins: {
				crosshair: false,
			},
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
					type: 'time',
				} ],
			},
			tooltips: {
				// Disable the on-canvas tooltip
				enabled: false,
				mode: 'index',
				intersect: false,
				custom: function( tooltipModel ) {
					// Tooltip Element
					let tooltipEl = document.getElementById( 'givewp-mini-chartjs-tooltip' );

					// Create element on first render
					if ( ! tooltipEl ) {
						tooltipEl = document.createElement( 'div' );
						tooltipEl.id = 'givewp-mini-chartjs-tooltip';
						tooltipEl.innerHTML = '<div class="givewp-tooltip-header"></div><div class="givewp-tooltip-body"><bold></b><br></div><div class="givewp-tooltip-caret"></div>';
						document.body.appendChild( tooltipEl );
					}

					// Hide if no tooltip
					if ( tooltipModel.opacity === 0 ) {
						tooltipEl.style.opacity = 0;
						tooltipEl.style.display = 'none';
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
					tooltipEl.style.display = 'block';
					tooltipEl.style.position = 'absolute';

					tooltipEl.style.left = position.left + tooltipModel.caretX + 'px';
					tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.caretY - ( tooltipEl.offsetHeight + 6 ) + 'px';

					tooltipEl.style.pointerEvents = 'none';

					const tooltip = data.datasets[ tooltipModel.dataPoints[ 0 ].datasetIndex ].tooltips[ tooltipModel.dataPoints[ 0 ].index ];

					// Setup tooltip inner HTML
					tooltipEl.innerHTML = `<div class="givewp-mini-tooltip-header">${ tooltip.title }</div><div class="givewp-mini-tooltip-body"><bold>${ tooltip.body }</b><br>${ tooltip.footer }</div><div class="givewp-mini-tooltip-caret"></div>`;
				},
			},
			elements: {
				point: {
					radius: 0,
					hitRadius: 3,
					hoverRadius: 4,
					backgroundColor: '#555555',
				},
			},
		},
	};
	return config;
}
