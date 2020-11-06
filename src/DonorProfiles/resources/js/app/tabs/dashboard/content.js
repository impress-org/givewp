import Heading from '../../components/heading';
import Stats from './stats';

const Content = () => {
	return (
		<div>
			<Heading icon="chart-line">
				Your Giving Stats
			</Heading>
			<Stats />
			<Heading icon="calendar-alt">
				Recent Donations
			</Heading>
		</div>
	);
};
export default Content;
