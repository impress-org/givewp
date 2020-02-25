// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import MiniChart from '../mini-chart';
import LoadingOverlay from '../loading-overlay';

// Store-related dependencies
import { useReportsAPI } from '../../utils';

const RESTMiniChart = ( { title, endpoint } ) => {
	// Use period from store
	const [ fetched, querying ] = useReportsAPI( endpoint );

	return (
		<Fragment>
			{ querying && (
				<LoadingOverlay />
			) }
			{ fetched && (
				<MiniChart
					title={ title }
					data={ fetched }
				/>
			) }
		</Fragment>
	);
};

RESTMiniChart.propTypes = {
	// Mini Chart title
	title: PropTypes.string.isRequired,
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
};

RESTMiniChart.defaultProps = {
	title: null,
	endpoint: null,
};

export default RESTMiniChart;
