import Chart from '../chart';
import './style.scss';

const SkeletonChart = ( { type, aspectRatio, showLegend } ) => {
	const skeletonData = {
		labels: [
			'--',
			'--',
			'--',
			'--',
			'--',
		],
		datasets: [
			{
				label: '--',
				data: [
					22,
					41,
					37,
					12,
					32,
				],
			},
		],
	};

	return (
		<div className="givewp-skeleton-chart">
			<Chart
				type={ type }
				aspectRatio={ aspectRatio }
				data={ skeletonData }
				showLegend={ showLegend }
			/>
		</div>
	);
};

export default SkeletonChart;
