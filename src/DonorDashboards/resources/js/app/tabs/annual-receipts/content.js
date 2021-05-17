import { Fragment, useEffect } from 'react';

import { __ } from '@wordpress/i18n';

import Heading from '../../components/heading';
import AnnualReceiptTable from '../../components/annual-receipt-table';

import { useSelector } from './hooks';
import { fetchAnnualReceiptsFromAPI } from './utils';

const Content = () => {
	const annualReceipts = useSelector( ( state ) => state.annualReceipts );
	const querying = useSelector( ( state ) => state.querying );
	const error = useSelector( ( state ) => state.error );

	const annualReceiptsCount = annualReceipts ? Object.entries( annualReceipts ).length : 0;

	useEffect( () => {
		fetchAnnualReceiptsFromAPI();
	}, [] );

	if ( error ) {
		return (
			<Fragment>
				<Heading icon="exclamation-triangle">
					{ __( 'Error', 'give' ) }
				</Heading>
				<p style={ { color: '#6b6b6b' } }>
					{ error }
				</p>
			</Fragment>
		);
	}

	return querying === true && annualReceipts === null ? (
		<Fragment>
			<Heading>
				{ __( 'Loading...', 'give' ) }
			</Heading>
			<AnnualReceiptTable />
		</Fragment>
	) : (
		<Fragment>
			<Heading>
				{ `${ annualReceiptsCount } ${ __( 'Total Annual Receipts', 'give' ) }` }
			</Heading>
			<AnnualReceiptTable annualReceipts={ annualReceipts } perPage={ 5 } />
		</Fragment>
	);
};
export default Content;
