// Vendor dependencies
import axios from 'axios';
import { useState, useEffect, Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import List from '../list';
import DonorItem from '../donor-item';
import LocationItem from '../location-item';
import DonationItem from '../donation-item';

// Store-related dependencies
import { useStoreValue } from '../../app/store';

const RESTList = ( { endpoint } ) => {
	// Use period from store
	const [ { period } ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	// Fetch new data and update List when period changes
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

	const items = Array.isArray( fetched ) && fetched.length ? fetched.map( ( item, index ) => {
		switch ( item.type ) {
			case 'donor':
				return <DonorItem
					image={ item.image }
					name={ item.name }
					email={ item.email }
					count={ item.count }
					total={ item.total }
					key={ index }
				/>;
			case 'donation':
				return <DonationItem
					status={ item.status }
					amount={ item.amount }
					time={ item.time }
					donor={ item.donor }
					source={ item.source }
					key={ index }
				/>;
			case 'location':
				return <LocationItem
					city={ item.city }
					state={ item.state }
					country={ item.country }
					flag={ item.flag }
					count={ item.count }
					total={ item.total }
					key={ index }
				/>;
		}
	} ) : null;

	return (
		<Fragment>
			{ fetched && (
				<List>
					{ items }
				</List>
			) }
		</Fragment>
	);
};

RESTList.propTypes = {
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
};

RESTList.defaultProps = {
	endpoint: null,
};

export default RESTList;
