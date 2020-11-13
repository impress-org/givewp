import Heading from '../../components/heading';
import DashboardContent from '../../components/dashboard-content';
import Stats from './stats';
import { Fragment } from 'react';

const Content = () => {
	return (
		<Fragment>
			<Heading icon="chart-line">
				Your Giving Stats
			</Heading>
			<Stats />
			<DashboardContent />
		</Fragment>
	);
};
export default Content;
