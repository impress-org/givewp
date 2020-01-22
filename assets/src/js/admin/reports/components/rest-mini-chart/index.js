// Vendor dependencies
import axios from 'axios';
import { useState, useEffect, Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import MiniChart from '../mini-chart';

// Store-related dependencies
import { useStoreValue } from '../../store';

const RESTMiniChart = ( { title, endpoint } ) => {
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
					data={ fetched }
				/>
			) }
		</Fragment>
	);
};

RESTMiniChart.propTypes = {
	// Mini Chart title
	title: PropTypes.string.isRequired,
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
};

RESTMiniChart.defaultProps = {
	title: null,
	endpoint: null,
};

export default RESTMiniChart;
