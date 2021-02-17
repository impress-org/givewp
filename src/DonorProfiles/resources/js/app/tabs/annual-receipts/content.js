import { Fragment, useEffect } from 'react';

const { __ } = wp.i18n;

import Heading from '../../components/heading';
import AnnualReceiptTable from '../../components/annual-receipt-table';

import { useSelector } from './hooks';
import { fetchAnnualReceiptsFromAPI } from './utils';

const Content = () => {
	const annualReceipts = useSelector( ( state ) => state.annualReceipts );
	const querying = useSelector( ( state ) => state.querying );

	useEffect( () => {
		fetchAnnualReceiptsFromAPI();
	}, [] );

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
				{ annualReceipts ? `${ Object.entries( annualReceipts ).length } ${ __( 'Total Annual Receipts', 'give' ) }` : __( '0 Total Annual Receipts', 'give' ) }
			</Heading>
			<AnnualReceiptTable annualReceipts={ annualReceipts } perPage={ 5 } />
		</Fragment>
	);
};
export default Content;
