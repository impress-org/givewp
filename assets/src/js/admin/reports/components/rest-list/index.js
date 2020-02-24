// Vendor dependencies
import axios from 'axios';
import { useState, useEffect, Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import List from '../list';
import LoadingOverlay from '../loading-overlay';

// Utilities
import { getItems, getSkeletonItems } from './utils';

// Store-related dependencies
import { useStoreValue } from '../../store';

const RESTList = ( { title, endpoint } ) => {
	// Use period from store
	const [ { period, giveStatus } ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	const [ loaded, setLoaded ] = useState( false );

	const [ querying, setQuerying ] = useState( false );

	const CancelToken = axios.CancelToken;
	const source = CancelToken.source();

	// Fetch new data and update List when period changes
	useEffect( () => {
		if ( period.startDate && period.endDate ) {
			if ( querying === true ) {
				source.cancel( 'Operation canceled by the user.' );
			}

			setQuerying( true );
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
					setQuerying( false );
					setFetched( response.data.data );
					setLoaded( true );
				} )
				.catch( function() {
					setQuerying( false );
				} );
		}
	}, [ period, endpoint ] );

	const items = getItems( fetched );
	const skeletonItems = getSkeletonItems();

	const donationsFound = giveStatus === 'donations_found' ? true : false;

	return (
		<Fragment>
			{ donationsFound ? (
				<Fragment>
					{ ! loaded && (
						<LoadingOverlay />
					) }
					<List title={ title }>
						{ items }
					</List>
				</Fragment>
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
