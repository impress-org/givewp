// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';

// Store-related dependencies
import { useReportsAPI } from '../../utils';

// Components
import Chart from '../chart';
import Spinner from '../spinner';

const RESTChart = ( { title, type, aspectRatio, endpoint, showLegend, headerElements } ) => {
	const [ fetched, querying ] = useReportsAPI( endpoint );

	const loadingStyle = {
		width: '100%',
		height: '295px',
	};

	return (
		<Fragment>
			{ title && (
				<div className="givewp-chart-title">
					<span className="givewp-chart-title-text">{ title }</span>
					{ querying && (
						<Spinner />
					) }
					{ headerElements && (
						headerElements
					) }
				</div>
			) }
			{ fetched ? (
				<Chart
					type={ type }
					aspectRatio={ aspectRatio }
					data={ fetched }
					showLegend={ showLegend }
				/>
			) : (
				<div style={ loadingStyle } />
			) }
		</Fragment>
	);
};

RESTChart.propTypes = {
	// Chart type (ex: line)
	type: PropTypes.string.isRequired,
	// Chart aspect ratio
	aspectRatio: PropTypes.number,
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
	// Display Chart with Legend
	showLegend: PropTypes.bool,
};

RESTChart.defaultProps = {
	type: null,
	aspectRatio: 0.6,
	endpoint: null,
	showLegend: false,
};

export default RESTChart;
