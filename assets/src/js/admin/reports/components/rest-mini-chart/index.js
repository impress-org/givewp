// Vendor dependencies
import axios from 'axios';
import { useState, useEffect, Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import MiniChart from '../mini-chart';

// Store-related dependencies
import { useStoreValue } from '../../store';

const RESTMiniChart = ( { title, highlight, endpoint } ) => {
	// Use period from store
	const [ { period } ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	// Fetch new data and update Chart when period changes
	useEffect( () => {
		if ( period.startDate && period.endDate ) {
			axios.get( wpApiSettings.root + 'give-api/v2/reports/' + endpoint, {
				params: {
					start: period.startDate.format( 'YYYY-MM-DD-HH' ),
					end: period.endDate.format( 'YYYY-MM-DD-HH' ),
				},
				headers: {
					'X-WP-Nonce': wpApiSettings.nonce,
				},
			} )
				.then( function( response ) {
					setFetched( response.data.data );
				} );
		}
	}, [ period, endpoint ] );

	return (
		<Fragment>
			{ fetched && (
				<MiniChart
					title={ title }
					highlight={ highlight }
					data={ fetched }
				/>
			) }
		</Fragment>
	);
};

RESTMiniChart.propTypes = {
	// Mini Chart title
	title: PropTypes.string.isRequired,
	// Mini Chart highlight value (ex total, average)
	highlight: PropTypes.string,
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
};

RESTMiniChart.defaultProps = {
	title: null,
	highlight: 'total',
	endpoint: null,
};

export default RESTMiniChart;
