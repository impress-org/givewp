import { useStoreValue } from '../store';
import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { setGiveStatus, setPageLoaded } from '../store/actions';
import { getSampleData } from './sample';

export const getWindowData = ( value ) => {
	const data = window.giveReportsData;
	return data[ value ];
};

/**
 * @since 4.16.8 Replaced axios with @wordpress/api-fetch. In-flight requests are now
 *            aborted from the effect cleanup rather than a cancel token recreated on
 *            every render, which never cancelled the request that was actually open.
 */
export const useReportsAPI = ( endpoint ) => {
	// Use period from store
	const [ { period, currency, testMode }, dispatch ] = useStoreValue();

	// Use state to hold data fetched from API
	const [ fetched, setFetched ] = useState( null );

	// Use state to hold querying status
	const [ querying, setQuerying ] = useState( false );

	// Fetch new data when period changes
	useEffect( () => {
		if ( ! period.startDate || ! period.endDate ) {
			return;
		}

		// Abort the previous request when the parameters change or the component unmounts
		const controller = new AbortController();

		const parameters = new URLSearchParams( {
			start: period.startDate.format( 'YYYY-MM-DD' ),
			end: period.endDate.format( 'YYYY-MM-DD' ),
			currency,
			testMode,
		} );

		setQuerying( true );
		apiFetch( {
			path: `/give-api/v2/reports/${ endpoint }?${ parameters.toString() }`,
			signal: controller.signal,
		} )
			.then( function( response ) {
				const status = response.status;
				dispatch( setGiveStatus( status ) );

				if ( status === 'no_donations_found' ) {
					const sample = getSampleData( endpoint );
					setFetched( sample );
				} else {
					setFetched( response.data );
				}

				if ( endpoint === 'income' ) {
					dispatch( setPageLoaded() );
				}

				setQuerying( false );
			} )
			.catch( function( error ) {
				// An aborted request is superseded by a newer one, which owns the querying state
				if ( error?.name !== 'AbortError' ) {
					setQuerying( false );
				}
			} );

		return () => controller.abort();
	}, [ period, currency, testMode, endpoint ] );

	return [ fetched, querying ];
};
