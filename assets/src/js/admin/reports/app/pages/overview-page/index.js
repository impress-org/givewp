// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../../components/grid';
import Card from '../../../components/card';
import RESTChart from '../../../components/rest-chart';
import RESTMiniChart from '../../../components/rest-mini-chart';
import List from '../../../components/list';
import RESTList from '../../../components/rest-list';
import LocationItem from '../../../components/location-item';
const { __ } = wp.i18n;

const OverviewPage = () => {
	return (
		<Grid>
			<Card title={ __( 'Donations vs Income', 'give' ) } width={ 12 }>
				<RESTChart
					type="line"
					aspectRatio={ 0.4 }
					endpoint="income"
					showLegend={ false }
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title={ __( 'Total Income', 'give' ) }
					endpoint="income"
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title={ __( 'Avg. Donation', 'give' ) }
					endpoint="average-donation"
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title={ __( 'Total Donors', 'give' ) }
					endpoint="donors"
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title={ __( 'Total Refunds', 'give' ) }
					endpoint="refunds"
				/>
			</Card>
			<Card title={ __( 'Payment Methods', 'give' ) } width={ 4 }>
				<RESTChart
					type="doughnut"
					aspectRatio={ 0.6 }
					endpoint="payment-methods"
					showLegend={ true }
				/>
			</Card>
			<Card title={ __( 'Payment Statuses', 'give' ) } width={ 4 }>
				<RESTChart
					type="bar"
					aspectRatio={ 1.2 }
					endpoint="payment-statuses"
					showLegend={ false }
				/>
			</Card>
			<Card title={ __( 'Form Performance (All Time)', 'give' ) } width={ 4 }>
				<RESTChart
					type="pie"
					aspectRatio={ 0.6 }
					endpoint="form-performance"
					showLegend={ true }
				/>
			</Card>
			<Card title={ __( 'Top Donors', 'give' ) } width={ 4 }>
				<RESTList endpoint="top-donors" />
			</Card>
			<Card title={ __( 'Location List', 'give' ) } width={ 4 }>
				<List>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
					<LocationItem
						city="Anacorts"
						state="Washington"
						country="United States"
						flag="flag.png"
						count="4 Donations"
						total="$345.00"
					/>
				</List>
			</Card>
			<Card title={ __( 'Recent Donations', 'give' ) } width={ 4 }>
				<RESTList endpoint="recent-donations" />
			</Card>
		</Grid>
	);
};
export default OverviewPage;
