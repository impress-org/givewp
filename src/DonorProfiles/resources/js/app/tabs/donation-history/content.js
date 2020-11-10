import { useLocation, Link } from 'react-router-dom';
import { Fragment } from 'react';

import Heading from '../../components/heading';
import RESTDonationTable from '../../components/rest-donation-table';
import DonationReceipt from '../../components/donation-receipt';

const Content = () => {
	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;

	return id ? (
		<Fragment>
			<Heading>
				Donation #{ id }
			</Heading>
			<DonationReceipt />
			<Link to="/donation-history">
				Back to Donation History
			</Link>
		</Fragment>
	) : (
		<Fragment>
			<Heading>
				10 Total Donations
			</Heading>
			<RESTDonationTable />
		</Fragment>
	);
};
export default Content;
