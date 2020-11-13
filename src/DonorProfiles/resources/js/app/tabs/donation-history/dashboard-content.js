import axios from 'axios';
import { Fragment, useState, useEffect } from 'react';

import DonationTable from '../../components/donation-table';
import Heading from '../../components/heading';

import { getAPIRoot, getAPINonce } from '../../utils';

const DashboardContent = () => {
	const [ donations, setDonations ] = useState( {} );
	useEffect( () => {
		axios.get( getAPIRoot() + 'give-api/v2/donor-profile/donations', {
			headers: {
				'X-WP-Nonce': getAPINonce(),
			},
		} )
			.then( ( response ) => response.data )
			.then( ( data ) => {
				setDonations( data.donations );
			} );
	}, [] );

	return <Fragment>
		<Heading icon="calendar-alt">
			Recent Donations
		</Heading>
		<DonationTable donations={ donations } />
	</Fragment>;
};
export default DashboardContent;
