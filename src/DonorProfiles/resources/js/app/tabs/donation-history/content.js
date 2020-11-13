import axios from 'axios';
import { useLocation, Link } from 'react-router-dom';
import { Fragment, useState, useEffect } from 'react';

import Heading from '../../components/heading';
import DonationReceipt from '../../components/donation-receipt';
import DonationTable from '../../components/donation-table';

import { getAPIRoot, getAPINonce } from '../../utils';

const Content = () => {
	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;
	const [ querying, setQuerying ] = useState( false );

	const [ donations, setDonations ] = useState( {} );
	useEffect( () => {
		setQuerying( true );
		axios.get( getAPIRoot() + 'give-api/v2/donor-profile/donations', {
			headers: {
				'X-WP-Nonce': getAPINonce(),
			},
		} )
			.then( ( response ) => response.data )
			.then( ( data ) => {
				setDonations( data.donations );
				setQuerying( false );
			} );
	}, [] );

	if ( id ) {
		return querying ? (
			<Fragment>
				<Heading>
					Loading...
				</Heading>
				<Link to="/donation-history">
					Back to Donation History
				</Link>
			</Fragment>
		) : (
			<Fragment>
				<Heading>
					Donation #{ id }
				</Heading>
				<DonationReceipt donation={ donations[ id ] } />
				<Link to="/donation-history">
					Back to Donation History
				</Link>
			</Fragment>
		);
	}
	return querying ? (
		<Fragment>
			<Heading>
				Loading
			</Heading>
			<DonationTable />
		</Fragment>
	) : (
		<Fragment>
			<Heading>
				10 Total Donations
			</Heading>
			<DonationTable donations={ donations } />
		</Fragment>
	);
};
export default Content;
