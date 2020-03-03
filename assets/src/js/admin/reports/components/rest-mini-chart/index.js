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

	const loadingStyle = {
		width: '100%',
		height: '95px',
	};

	return (
		<Fragment>
			{ querying && (
				<LoadingOverlay />
			) }
			{ fetched ? (
				<MiniChart
					title={ title }
					data={ fetched }
				/>
			) : (
				<div style={ loadingStyle } />
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
