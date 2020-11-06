import Heading from '../../components/heading';
import Stats from './stats';
import { Fragment } from 'react';

const Content = () => {
	return (
		<Fragment>
			<Heading icon="chart-line">
				Your Giving Stats
			</Heading>
			<Stats />
			<Heading icon="calendar-alt">
				Recent Donations
			</Heading>
		</Fragment>
	);
};
export default Content;
