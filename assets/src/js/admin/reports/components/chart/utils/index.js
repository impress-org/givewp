import crosshairPlugin from './crosshair';

// Format data from Reports API for ChartJS
export function formatData( type, data ) {
	const formattedLabels = data.labels ? data.labels.slice( 0 ) : null;

	const formattedDatasets = data.datasets.map( ( dataset, index ) => {
		// Setup styles
		const styles = createStyles( type, dataset.data, index );

		const formatted = {
			data: dataset.data.slice( 0 ),
			yAxisID: `y-axis-${ index }`,
			backgroundColor: styles.backgroundColor,
			borderColor: styles.borderColor,
			borderWidth: styles.borderWidth,
		};

		return formatted;
	} );

	const formattedData = {
		labels: formattedLabels,
		datasets: formattedDatasets,
	};

	return formattedData;
}

// Create chart styles from predifined pallette,
// depending on chart type
function createStyles( type, data, index ) {
	const palette = [
		'#69B868',
		'#556E79',
		'#9EA3A8',
		'#4BB5D7',
		'#F49420',
		'#D75A4B',
		'#914BD7',
	];

	const styles = {
		backgroundColor: palette,
		borderColor: palette,
		borderWidth: 0,
	};

	// Handle special styles needed for 'line' and 'doughnut' charts
	switch ( type ) {
		case 'line':
			styles.backgroundColor = [
				palette[ index ] + '44',
			];
			styles.borderColor = [
				palette[ index ],
			];
			styles.borderWidth = 3;
			break;
		case 'pie':
		case 'doughnut':
			styles.borderColor = [ 'rgb(244, 244, 244)' ];
			styles.borderWidth = 3;
	}

	return styles;
}

// Return config object for ChartJS
export function createConfig( type, data ) {
	const formattedData = formatData( type, data );
	const intersect = type === 'line' || type === 'bar' ? false : true;
	const config = {
		type: type,
		data: formattedData,
		options: {
			hover: {
				intersect: intersect,
			},
			legend: {
				display: false,
			},
			layout: {
				padding: 16,
			},
			scales: {
				xAxes: [],
				yAxes: [],
			},
			elements: {
				point: {
					radius: 4,
					hitRadius: 2,
					hoverRadius: 6,
					backgroundColor: '#69B868',
				},
			},
			tooltips: {
				// Disable the on-canvas tooltip
				enabled: false,
				mode: 'index',
				intersect: intersect,
				custom: function( tooltipModel ) {
					// Tooltip Element
					let tooltipEl = document.getElementById( 'givewp-chartjs-tooltip' );

					// Create element on first render
					if ( ! tooltipEl ) {
						tooltipEl = document.createElement( 'div' );
						tooltipEl.id = 'givewp-chartjs-tooltip';
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

					// Set tooltip inner HTML
					tooltipEl.innerHTML = `<div class="givewp-tooltip-header">${ tooltip.title }</div><div class="givewp-tooltip-body"><bold>${ tooltip.body }</b><br>${ tooltip.footer }</div><div class="givewp-tooltip-caret"></div>`;
				},
			},
		},
	};

	// Setup yAxes to begin at zero if chart is 'line' or 'bar'
	if ( type === 'line' || type === 'bar' ) {
		const yAxes = data.datasets.map( ( dataset, index ) => {
			let max;
			switch ( typeof dataset.data[ 0 ] ) {
				case 'object': {
					const calcMax = Math.max( ...dataset.data.map( o => o.y ), 0 ) * 1.1;
					switch ( type ) {
						case 'line':
							max = calcMax > 100 ? calcMax : 100;
							break;
						case 'bar':
							max = calcMax > 10 ? calcMax : 10;
							break;
					}
					break;
				}
				default: {
					const calcMax = Math.max( ...dataset.data.map( o => o ), 0 ) * 1.1;
					switch ( type ) {
						case 'line':
							max = calcMax > 100 ? calcMax : 100;
							break;
						case 'bar':
							max = calcMax > 10 ? calcMax : 10;
							break;
					}
					break;
				}
			}

			return {
				gridLines: {
					color: '#D8D8D8',
				},
				id: `y-axis-${ index }`,
				ticks: {
					suggestedMax: max,
					beginAtZero: true,
				},
			};
		} );

		config.options.scales = {
			yAxes: yAxes,
			xAxes: [],
		};

		if ( type === 'line' ) {
			const count = data.datasets[ 0 ].data.length;
			const ticksSource = count < 16 ? 'data' : 'auto';
			const firstYear = parseInt( data.datasets[ 0 ].data[ 0 ].x );
			const currentYear = new Date().getFullYear();
			const dayFormat = firstYear === currentYear ? 'MMM D' : 'MMM D, YYYY';

			config.options.scales.xAxes = [ {
				gridLines: {
					display: false,
				},
				type: 'time',
				time: {
					displayFormats: {
						hour: 'ddd ha',
						day: dayFormat,
					},
				},
				ticks: {
					maxTicksLimit: 10,
					source: ticksSource,
				},
			} ];

			config.plugins = [ crosshairPlugin ];
		}
	}

	return config;
}
