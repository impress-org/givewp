// Reports admin dashboard widget

// Components
import Grid from '../components/grid';
import Card from '../components/card';
import Chart from '../components/chart';
import RESTMiniChart from '../components/rest-mini-chart';

// Dependencies
const { __ } = wp.i18n;

const Widget = () => {
	return (
		<Grid gap="12px">
			<Card width={ 12 }>
				<Chart
					type="line"
					aspectRatio={ 0.4 }
					data={ {
						labels: [ 'Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul' ],
						datasets: [
							{
								label: 'Donations',
								data: [ 4, 5, 3, 7, 5, 6 ],
							},
						],
					} }
				/>
			</Card>
			<Card width={ 6 }>
				<RESTMiniChart
					title={ __( 'Total Income', 'give' ) }
					highlight="total"
					endpoint="income-over-time"
				/>
			</Card>
			<Card width={ 6 }>
				<RESTMiniChart
					title={ __( 'Total Income', 'give' ) }
					highlight="total"
					endpoint="income-over-time"
				/>
			</Card>
			<Card width={ 6 }>
				<RESTMiniChart
					title={ __( 'Total Income', 'give' ) }
					highlight="total"
					endpoint="income-over-time"
				/>
			</Card>
			<Card width={ 6 }>
				<RESTMiniChart
					title={ __( 'Total Income', 'give' ) }
					highlight="total"
					endpoint="income-over-time"
				/>
			</Card>
		</Grid>
	);
};
export default Widget;
