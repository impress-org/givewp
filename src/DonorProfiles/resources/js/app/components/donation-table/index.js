import { Fragment, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import Table from '../table';
import DonationRow from './donation-row';

import './style.scss';

const DonationTable = ( { donations, perPage } ) => {
	const [ page, setPage ] = useState( 1 );

	let donationRows = [];
	let start = 0;
	let end = perPage;
	let lastPage = 1;

	if ( donations ) {
		start = ( page - 1 ) * perPage;
		end = start + perPage <= Object.entries( donations ).length ? start + perPage : Object.entries( donations ).length;
		lastPage = Math.ceil( Object.entries( donations ).length / perPage );

		donationRows = Object.entries( donations ).reduce( ( rows, donation, index ) => {
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
						Donation
					</div>
					<div className="give-donor-profile-table__column">
						Campaign
					</div>
					<div className="give-donor-profile-table__column">
						Date
					</div>
					<div className="give-donor-profile-table__column">
						Status
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
						{ donations && `Showing ${ start + 1 } - ${ end } of ${ Object.entries( donations ).length } Donations` }
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
