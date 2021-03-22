import { Fragment, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { __ } from '@wordpress/i18n';
;

import Table from '../table';
import AnnualReceiptRow from './annual-receipt-row';

import './style.scss';

const AnnualReceiptTable = ( { annualReceipts, perPage } ) => {
	const [ page, setPage ] = useState( 1 );

	const getStartIndex = () => {
		return ( page - 1 ) * perPage;
	};

	const getEndIndex = () => {
		return start + perPage <= annualReceiptsArray.length ? start + perPage : annualReceiptsArray.length;
	};

	const getAnnualReceiptRows = () => {
		return annualReceiptsArray.reduce( ( rows, annualReceipt, index ) => {
			if ( index >= start && index < end ) {
				rows.push( <AnnualReceiptRow key={ index } annualReceipt={ annualReceipt } /> );
			}
			return rows;
		}, [] );
	};

	let annualReceiptRows = [];
	let annualReceiptsArray = [];
	let start = 0;
	let end = perPage;
	let lastPage = 1;

	if ( annualReceipts ) {
		annualReceiptsArray = Object.entries( annualReceipts );
		start = getStartIndex();
		end = getEndIndex();
		lastPage = Math.ceil( annualReceiptsArray.length / perPage ) - 1;
		annualReceiptRows = getAnnualReceiptRows();
	}

	return (
		<Table
			header={
				<Fragment>
					<div className="give-donor-dashboard-table__column">
						{ __( 'Year', 'give' ) }
					</div>
					<div className="give-donor-dashboard-table__column">
						{ __( 'Amount', 'give' ) }
					</div>
					<div className="give-donor-dashboard-table__column">
						{ __( 'Count', 'give' ) }
					</div>
					<div className="give-donor-dashboard-table__column">
						{ __( 'Statement', 'give' ) }
					</div>
				</Fragment>
			}

			rows={
				<Fragment>
					{ annualReceiptRows }
				</Fragment>
			}

			footer={
				<Fragment>
					<div className="give-donor-dashboard-table__footer-text">
						{ annualReceipts && `${ __( 'Showing', 'give' ) } ${ start + 1 } - ${ end } ${ __( 'of', 'give' ) } ${ annualReceiptsArray.length } ${ __( 'Receipts', 'give' ) }` }
					</div>
					<div className="give-donor-dashboard-table__footer-nav">
						{ page - 1 >= 1 && (
							<a onClick={ () => setPage( page - 1 ) }>
								<FontAwesomeIcon icon="chevron-left" />
							</a>
						) }
						{ page <= lastPage && (
							<a onClick={ () => setPage( page + 1 ) }>
								<FontAwesomeIcon icon="chevron-right" />
							</a>
						) }
					</div>
				</Fragment>
			}
		/>
	);
};

export default AnnualReceiptTable;
