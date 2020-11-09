import Heading from '../../components/heading';
import RESTSubscriptionTable from '../../components/rest-subscription-table';

import { Fragment } from 'react';

const Content = () => {
	return (
		<Fragment>
			<Heading>
				2 Recurring Donations
			</Heading>
			<RESTSubscriptionTable />
		</Fragment>
	);
};
export default Content;
