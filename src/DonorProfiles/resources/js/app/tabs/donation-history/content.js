import Heading from '../../components/heading';
import RESTDonationTable from '../../components/rest-donation-table';
import { Fragment } from 'react';

const Content = () => {
	return (
		<Fragment>
			<Heading>
				10 Total Donations
			</Heading>
			<RESTDonationTable />
		</Fragment>
	);
};
export default Content;
