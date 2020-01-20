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
import DonationItem from '../../../components/donation-item';
const { __ } = wp.i18n;

const OverviewPage = () => {
	return (
		<Grid>
			<Card title={ __( 'Donations vs Income', 'give' ) } width={ 12 }>
				<RESTChart
					type="line"
					aspectRatio={ 0.4 }
					endpoint="donations-vs-income"
					showLegend={ false }
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title="Total Income"
					highlight="total"
					endpoint="income-over-time"
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title="Avg. Income"
					highlight="average"
					endpoint="income-over-time"
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title="Total Income"
					highlight="total"
					endpoint="income-over-time"
				/>
			</Card>
			<Card width={ 3 }>
				<RESTMiniChart
					title="Total Income"
					highlight="total"
					endpoint="income-over-time"
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
			<Card title={ __( 'Donor List', 'give' ) } width={ 4 }>
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
			<Card title={ __( 'Donation List', 'give' ) } width={ 4 }>
				<List>
					<DonationItem
						status="completed"
						amount="$50.00"
						time="2013-02-08 09:30"
						donor={ { name: 'Test Name', id: 456 } }
						source="Save the Whales"
					/>
					<DonationItem
						status="completed"
						amount="$50.00"
						time="2013-02-08 09:30"
						donor={ { name: 'Test Name', id: 456 } }
						source="Save the Whales"
					/>
					<DonationItem
						status="abandoned"
						amount="$50.00"
						time="2013-02-08 09:30"
						donor={ { name: 'Test Name', id: 456 } }
						source="Save the Whales"
					/>
					<DonationItem
						status="refunded"
						amount="$50.00"
						time="2013-02-08 09:30"
						donor={ { name: 'Test Name', id: 456 } }
						source="Save the Whales"
					/>
					<DonationItem
						status="completed"
						amount="$50.00"
						time="2013-02-08 09:30"
						donor={ { name: 'Test Name', id: 456 } }
						source="Save the Whales"
					/>
					<DonationItem
						status="completed"
						amount="$50.00"
						time="2013-02-08 09:30"
						donor={ { name: 'Test Name', id: 456 } }
						source="Save the Whales"
					/>
				</List>
			</Card>
		</Grid>
	);
};
export default OverviewPage;
