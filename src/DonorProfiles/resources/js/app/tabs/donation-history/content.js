import { useLocation } from 'react-router-dom';
import { Fragment } from 'react';

import Heading from '../../components/heading';
import RESTDonationTable from '../../components/rest-donation-table';

const Content = () => {
	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;

	return id ? (
		<Fragment>
			<Heading>
				Donation ID: { id }
			</Heading>
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
