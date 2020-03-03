// Dependencies
import PropTypes from 'prop-types';
import { getSkeletonData } from './utils';

// Components
import Chart from '../chart';

// Styles
import './style.scss';

const SkeletonChart = ( { type, aspectRatio, showLegend } ) => {
	const skeletonData = getSkeletonData( type );

	return (
		<Chart
			type={ type }
			aspectRatio={ aspectRatio }
			data={ skeletonData }
			showLegend={ showLegend }
		/>
	);
};

SkeletonChart.propTypes = {
	// Type of chart to render
	type: PropTypes.string.isRequired,
	// Aspect ratio of chart (defaults to 1)
	aspectRatio: PropTypes.number,
	// Whether to show Chart legend (defaults to true)
	showLegend: PropTypes.bool,
};

SkeletonChart.default = {
	type: null,
	aspectRatio: 1,
	showLegend: true,
};

export default SkeletonChart;
