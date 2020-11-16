import { useLocation, Link } from 'react-router-dom';
import { Fragment } from 'react';

import Heading from '../../components/heading';
import DonationReceipt from '../../components/donation-receipt';
import DonationTable from '../../components/donation-table';

import { useSelector } from './hooks';

const Content = () => {
	const donations = useSelector( ( state ) => state.donations );
	const querying = useSelector( ( state ) => state.querying );

	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;

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
