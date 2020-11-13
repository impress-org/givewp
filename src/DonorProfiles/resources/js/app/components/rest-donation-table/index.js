import axios from 'axios';
import { Fragment, useEffect, useState } from 'react';

import Table from '../table';
import DonationRow from './donation-row';
import { getAPIRoot, getAPINonce } from '../../utils';

const RESTDonationTable = () => {
	const [ donations, setDonations ] = useState( [] );
	const [ donationRows, setDonationRows ] = useState( null );

	useEffect( () => {
		axios.get( getAPIRoot() + 'give-api/v2/donor-profile/donation-history', {
			headers: {
				'X-WP-Nonce': getAPINonce(),
			},
		} )
			.then( ( response ) => response.data )
			.then( ( data ) => {
				setDonations( data.donations );
			} );
	}, [] );

	useEffect( () => {
		if ( donations ) {
			setDonationRows( Object.entries( donations ).map( ( donation, index ) => <DonationRow donation={ donation } key={ index } /> ) );
		}
	}, [ donations ] );

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
						Showing 1-5 of 10 Donations
					</div>
					<div className="give-donor-profile-table__footer-nav">
						Buttons
					</div>
				</Fragment>
			}
		/>
	);
};

export default RESTDonationTable;
