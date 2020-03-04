const defaultOptions = {
	line: {
		color: '#9EA3A8',
		width: 1,
		dashPattern: [ 5, 10 ],
	},
	snap: {
		enabled: true,
	},
};

const crosshairPlugin = {

	id: 'crosshair',

	afterInit: function( chart ) {
		if ( chart.config.options.scales.xAxes.length === 0 ) {
			return;
		}

		const xScaleType = chart.config.options.scales.xAxes[ 0 ].type;

		if ( xScaleType !== 'linear' && xScaleType !== 'time' ) {
			return;
		}

		chart.crosshair = {
			enabled: false,
			x: null,
		};
	},

	getXScale: function( chart ) {
		return chart.data.datasets.length ? chart.scales[ chart.getDatasetMeta( 0 ).xAxisID ] : null;
	},
	getYScale: function( chart ) {
		return chart.scales[ chart.getDatasetMeta( 0 ).yAxisID ];
	},

	afterEvent: function( chart, e ) {
		if ( chart.config.options.scales.xAxes.length === 0 ) {
			return;
		}

		const xScaleType = chart.config.options.scales.xAxes[ 0 ].type;

		if ( xScaleType !== 'linear' && xScaleType !== 'time' ) {
			return;
		}

		const xScale = this.getXScale( chart );

		if ( ! xScale ) {
			return;
		}

		chart.crosshair.enabled = ( e.type !== 'mouseout' && ( e.x > xScale.getPixelForValue( xScale.min ) && e.x < xScale.getPixelForValue( xScale.max ) ) );

		if ( ! chart.crosshair.enabled ) {
			if ( e.x > xScale.getPixelForValue( xScale.max ) ) {
				chart.update();
			}
			return true;
		}

		chart.crosshair.x = e.x;

		chart.draw();
	},

	afterDraw: function( chart ) {
		if ( ! chart.crosshair.enabled ) {
			const tooltipEl = document.getElementById( 'givewp-chartjs-tooltip' );
			if ( tooltipEl ) {
				tooltipEl.style.opacity = 0;
				tooltipEl.style.display = 'none';
			}

			return;
		}

		this.drawTracePoints( chart );
		this.drawTraceLine( chart );

		return true;
	},

	drawTraceLine: function( chart ) {
		const yScale = this.getYScale( chart );
		const xScale = this.getXScale( chart );

		let lineX = chart.crosshair.x;
		const isHoverIntersectOff = chart.config.options.hover.intersect === false;
		const snapEnabled = defaultOptions.snap.enabled;

		if ( snapEnabled && isHoverIntersectOff && chart.active.length ) {
			lineX = chart.active[ 0 ]._view.x;
		}

		const lineWidth = defaultOptions.line.width;
		const color = defaultOptions.line.color;
		const dashPattern = defaultOptions.line.dashPattern;

		chart.ctx.beginPath();
		chart.ctx.setLineDash( dashPattern );
		chart.ctx.moveTo( lineX, yScale.getPixelForValue( yScale.max ) );
		chart.ctx.lineWidth = lineWidth;
		chart.ctx.strokeStyle = color;
		chart.ctx.lineTo( lineX, yScale.getPixelForValue( yScale.min ) );
		chart.ctx.stroke();
		chart.ctx.setLineDash( [] );

		// Draw shaodw
		chart.ctx.beginPath();
		chart.ctx.fillStyle = 'rgba(35, 36, 37, 0.05)';

		const leftEnd = xScale.getPixelForValue( xScale.min );
		const rightEnd = xScale.getPixelForValue( xScale.max );

		let x;
		let width;

		if ( lineX - 70 < leftEnd ) {
			x = leftEnd;
			width = 70 + ( lineX - leftEnd );
		} else if ( lineX + 70 > rightEnd ) {
			x = lineX - 70;
			width = 70 + ( rightEnd - lineX );
		} else {
			x = lineX - 70;
			width = 140;
		}

		const y = yScale.getPixelForValue( yScale.max );
		const height = yScale.getPixelForValue( yScale.min ) - yScale.getPixelForValue( yScale.max );
		chart.ctx.rect( x, y, width, height );
		chart.ctx.fill();
	},

	drawTracePoints: function( chart ) {
		for ( let chartIndex = 0; chartIndex < chart.data.datasets.length; chartIndex++ ) {
			const dataset = chart.data.datasets[ chartIndex ];
			const meta = chart.getDatasetMeta( chartIndex );

			const yScale = chart.scales[ meta.yAxisID ];

			if ( meta.hidden || ! dataset.interpolate ) {
				continue;
			}

			chart.ctx.beginPath();
			chart.ctx.arc( chart.crosshair.x, yScale.getPixelForValue( dataset.interpolatedValue ), 3, 0, 2 * Math.PI, false );
			chart.ctx.fillStyle = 'white';
			chart.ctx.lineWidth = 2;
			chart.ctx.strokeStyle = dataset.borderColor;
			chart.ctx.fill();
			chart.ctx.stroke();
		}
	},

};

export default crosshairPlugin;
