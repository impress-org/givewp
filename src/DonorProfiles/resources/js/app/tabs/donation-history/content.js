import { useLocation, Link } from 'react-router-dom';
import { Fragment } from 'react';

const { __ } = wp.i18n;

import Heading from '../../components/heading';
import DonationReceipt from '../../components/donation-receipt';
import DonationTable from '../../components/donation-table';

import { useSelector } from './hooks';

const Content = () => {
	const donations = useSelector( ( state ) => state.donations );
	const querying = useSelector( ( state ) => state.querying );

	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;

	const getDonationById = ( donationId ) => {
		const filter = donations.filter( ( donation ) => donation.id === parseInt( donationId ) ? true : false );
		if ( filter.length ) {
			return filter[ 0 ];
		}
		return null;
	};

	if ( id ) {
		return querying ? (
			<Fragment>
				<Heading>
					{ __( 'Loading...', 'give' ) }
				</Heading>
				<Link to="/donation-history">
					{ __( 'Back to Donation History', 'give' ) }
				</Link>
			</Fragment>
		) : (
			<Fragment>
				<Heading>
					{ __( 'Donation', 'give' ) } #{ id }
				</Heading>
				<DonationReceipt donation={ getDonationById( id ) } />
				<Link to="/donation-history">
					{ __( 'Back to Donation History', 'give' ) }
				</Link>
			</Fragment>
		);
	}

	return querying === true ? (
		<Fragment>
			<Heading>
				{ __( 'Loading...', 'give' ) }
			</Heading>
			<DonationTable />
		</Fragment>
	) : (
		<Fragment>
			<Heading>
				{ `${ Object.entries( donations ).length } ${ __( 'Total Donations', 'give' ) }` }
			</Heading>
			<DonationTable donations={ donations } perPage={ 5 } />
		</Fragment>
	);
};
export default Content;
