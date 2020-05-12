import { useStoreValue } from '../store';
import { useState, useEffect } from 'react';
import axios from 'axios';
import { setGiveStatus, setPageLoaded } from '../store/actions';
import { getSampleData } from './sample';

export const getWindowData = ( value ) => {
	const data = window.giveReportsData;
	return data[ value ];
};

export const useReportsAPI = ( endpoint ) => {
	// Use period from store
	const [ { period, currency, testMode }, dispatch ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	// Use state to hold querying status
	const [ querying, setQuerying ] = useState( false );

	// Setup cancel token for request
	const CancelToken = axios.CancelToken;
	const source = CancelToken.source();

	// Fetch new data when period changes
	useEffect( () => {
		if ( period.startDate && period.endDate ) {
			if ( querying === true ) {
				source.cancel( 'Operation canceled by the user.' );
			}
			setQuerying( true );
			axios.get( wpApiSettings.root + 'give-api/v2/reports/' + endpoint, {
				cancelToken: source.token,
				params: {
					start: period.startDate.format( 'YYYY-MM-DD' ),
					end: period.endDate.format( 'YYYY-MM-DD' ),
					currency: currency,
					testMode: testMode,
				},
				headers: {
					'X-WP-Nonce': wpApiSettings.nonce,
				},
			} )
				.then( function( response ) {
					const status = response.data.status;
					dispatch( setGiveStatus( status ) );

					if ( status === 'no_donations_found' ) {
						const sample = getSampleData( endpoint );
						setFetched( sample );
					} else {
						setFetched( response.data.data );
					}

					if ( endpoint === 'income' ) {
						dispatch( setPageLoaded() );
					}

					setQuerying( false );
				} )
				.catch( function() {
					setQuerying( false );
				} );
		}
	}, [ period, currency, testMode, endpoint ] );

	return [ fetched, querying ];
};
