// Vendor dependencies
import axios from 'axios';
import { useState, useEffect, Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import List from '../list';
import LoadingOverlay from '../loading-overlay';
import NotFoundOverlay from '../not-found-overlay';

// Utilities
import { getItems, getSkeletonItems } from './utils';

// Store-related dependencies
import { useStoreValue } from '../../store';

const RESTList = ( { title, endpoint } ) => {
	// Use period from store
	const [ { period } ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	const [ dataFound, setDataFound ] = useState( true );

	const [ loaded, setLoaded ] = useState( false );

	// Fetch new data and update List when period changes
	useEffect( () => {
		if ( period.startDate && period.endDate ) {
			setLoaded( false );
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
					const found = response.data.data.length > 0 ? true : false;
					setDataFound( found );
					setLoaded( true );
				} );
		}
	}, [ period, endpoint ] );

	const items = getItems( fetched );
	const skeletonItems = getSkeletonItems();

	let overlay;
	switch ( true ) {
		case loaded === false: {
			overlay = <LoadingOverlay />;
			break;
		}
		case dataFound === false: {
			overlay = <NotFoundOverlay />;
			break;
		}
	}

	return (
		<Fragment>
			{ overlay }
			{ fetched ? (
				<List title={ title }>
					{ items }
				</List>
			) : (
				<List title={ title }>
					{ skeletonItems }
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
