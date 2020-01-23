// Reports admin dashboard widget

// Components
import Grid from '../components/grid';
import Card from '../components/card';
import Chart from '../components/chart';
import RESTMiniChart from '../components/rest-mini-chart';

// Dependencies
import moment from 'moment';
const { __ } = wp.i18n;

// Store related dependencies
import { StoreProvider } from '../store';
import { reducer } from '../store/reducer';

const Widget = () => {
	// Initial app state (available in component through useStoreValue)
	const initialState = {
		// Initial period range (defaults to the past week)
		period: {
			startDate: moment().subtract( 7, 'days' ),
			endDate: moment(),
			range: 'week',
		},
	};

	return (
		<StoreProvider initialState={ initialState } reducer={ reducer }>
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
						endpoint="income-over-time"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Total Income', 'give' ) }
						endpoint="income-over-time"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Total Income', 'give' ) }
						endpoint="income-over-time"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Total Income', 'give' ) }
						endpoint="income-over-time"
					/>
				</Card>
			</Grid>
		</StoreProvider>
	);
};
export default Widget;
