import { Fragment, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

import Table from '../table';
import DonationRow from './donation-row';

import './style.scss';

const DonationTable = ( { donations, perPage } ) => {
	const [ page, setPage ] = useState( 1 );

	let donationRows = [];
	let donationsArray = [];
	let start = 0;
	let end = perPage;
	let lastPage = 1;

	if ( donations ) {
		donationsArray = Object.entries( donations );
		start = ( page - 1 ) * perPage;
		end = start + perPage <= donationsArray.length ? start + perPage : donationsArray.length;
		lastPage = Math.ceil( donationsArray.length / perPage );
		donationRows = donationsArray.reduce( ( rows, donation, index ) => {
			if ( index >= start && index < end ) {
				rows.push( <DonationRow donation={ donation } /> );
			}
			return rows;
		}, [] );
	}

	return (
		<Table
			header={
				<Fragment>
					<div className="give-donor-profile-table__column">
						{ __( 'Donation', 'give' ) }
					</div>
					<div className="give-donor-profile-table__column">
						{ __( 'Campaign', 'give' ) }
					</div>
					<div className="give-donor-profile-table__column">
						{ __( 'Date', 'give' ) }
					</div>
					<div className="give-donor-profile-table__column">
						{ __( 'Status', 'give' ) }
					</div>
				</Fragment>
			}

			rows={
				<Fragment>
					{ donationRows }
				</Fragment>
			}

			footer={
				<Fragment>
					<div className="give-donor-profile-table__footer-text">
						{ donations && `${ __( 'Showing', 'give' ) } ${ start + 1 } - ${ end } ${ __( 'of', 'give' ) } ${ donationsArray.length } ${ __( 'Donations', 'give' ) }` }
					</div>
					<div className="give-donor-profile-table__footer-nav">
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

export default DonationTable;
