import { Fragment } from 'react';

import DonationTable from '../../components/donation-table';
import Heading from '../../components/heading';

import { useSelector } from './hooks';

const DashboardContent = () => {
	const donations = useSelector( ( state ) => state.donations );
	const querying = useSelector( ( state ) => state.querying );

	return <Fragment>
		<Heading icon="calendar-alt">
			{ querying ? 'Loading...' : 'Recent Donations' }
		</Heading>
		{ ! querying && (
			<DonationTable donations={ donations } perPage={ 3 } />
		) }
	</Fragment>;
};
export default DashboardContent;
