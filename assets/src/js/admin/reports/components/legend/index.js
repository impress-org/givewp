// Dependencies
import PropTypes from 'prop-types';
import { useState } from 'react';

// Utilities
import { getColor } from './utils';

// Styles
import './style.scss';

const Legend = ( { data, chartRef } ) => {
	// Calculate total value of points in dataset (used to find percentages)
	const total = data.datasets[ 0 ].data.length > 0 ? data.datasets[ 0 ].data.reduce( ( a, b ) => parseInt( a ) + parseInt( b ) ) : 0;

	const [ inactiveItems, setInactiveItems ] = useState( [] );

	// Hide a given datapoint in the chart and the coresponding legend item
	const hideData = ( index, value ) => {
		const inactive = inactiveItems.slice( 0 );
		if ( chartRef.data.datasets[ 0 ].data[ index ] === value ) {
			chartRef.data.datasets[ 0 ].data[ index ] = null;
			inactive.push( index );
			setInactiveItems( inactive );
		} else {
			chartRef.data.datasets[ 0 ].data[ index ] = value;
			const foundIndex = inactive.indexOf( index );
			inactive.splice( foundIndex, 1 );
			setInactiveItems( inactive );
		}
		chartRef.update();
	};

	// Map dataset to build legend items
	const items = data.datasets[ 0 ].data.map( ( amount, index ) => {
		// Prepare status class for item
		const status = inactiveItems.includes( index ) ? ' inactive' : '';

		// Use ulitity function to match item color to chart color
		const color = getColor( index );

		// Prepare percent to display with legend item
		const percent = Math.round( ( amount / total ) * 100 );
		const percentText = ! isNaN( percent ) ? percent + '%' : '0%';

		const point = data.datasets[ 0 ].tooltips[ index ];

		return (
			<div className={ 'item' + status } key={ index } onClick={ () => hideData( index, amount ) }>
				<div className="bar" style={ { background: color } }></div>
				<p className="label">{ point.footer }</p>
				<p className="data">{ point.title }</p>
				<p className="percent">{ percentText }</p>
			</div>
		);
	} );
	return (
		<div className="givewp-legend">
			{ items }
		</div>
	);
};

Legend.propTypes = {
	// Data object provided from Reports API
	data: PropTypes.object.isRequired,
	// Chart object created and passed by parent Chart component
	chartRef: PropTypes.object.isRequired,
};

Legend.defaultProps = {
	data: null,
	chartRef: null,
};

export default Legend;
