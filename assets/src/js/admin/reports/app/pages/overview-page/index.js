// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

// Vendor dependencies
const { __ } = wp.i18n;
import { Fragment } from 'react';

// Store-related dependencies
import { useStoreValue } from '../../../store';

//Components
import Grid from '../../../components/grid';
import Card from '../../../components/card';
import RESTChart from '../../../components/rest-chart';
import RESTMiniChart from '../../../components/rest-mini-chart';
import RESTList from '../../../components/rest-list';
import List from '../../../components/list';
import LocationItem from '../../../components/location-item';
import NotFoundNotice from '../../../components/not-found-notice';
import LoadingNotice from '../../../components/loading-notice';

const OverviewPage = () => {
	// Use initLoaded from store
	const [ { donationsFound, pageLoaded } ] = useStoreValue();
	const showGrid = donationsFound && pageLoaded ? true : false;
	return (
		<Fragment>
			{ donationsFound === false && (
				<NotFoundNotice />
			) }
			{ pageLoaded === false && (
				<LoadingNotice />
			) }
			<Grid visible={ showGrid }>
				<Card width={ 12 }>
					<RESTChart
						title={ __( 'Income for Period', 'give' ) }
						type="line"
						aspectRatio={ 0.4 }
						endpoint="income"
						showLegend={ false }
					/>
				</Card>
				<Card width={ 3 }>
					<RESTMiniChart
						title={ __( 'Total Income', 'give' ) }
						endpoint="total-income"
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
						endpoint="total-refunds"
					/>
				</Card>
				<Card width={ 4 }>
					<RESTChart
						title={ __( 'Payment Methods', 'give' ) }
						type="doughnut"
						aspectRatio={ 0.6 }
						endpoint="payment-methods"
						showLegend={ true }
					/>
				</Card>
				<Card width={ 4 }>
					<RESTChart
						title={ __( 'Payment Statuses', 'give' ) }
						type="bar"
						aspectRatio={ 1.2 }
						endpoint="payment-statuses"
						showLegend={ false }
					/>
				</Card>
				<Card width={ 4 }>
					<RESTChart
						title={ __( 'Form Performance (All Time)', 'give' ) }
						type="pie"
						aspectRatio={ 0.6 }
						endpoint="form-performance"
						showLegend={ true }
					/>
				</Card>
				<Card width={ 4 }>
					<RESTList
						title={ __( 'Top Donors', 'give' ) }
						endpoint="top-donors"
					/>
				</Card>
				<Card title={ __( 'Location List', 'give' ) } width={ 4 }>
					<List>
						<LocationItem
							city="Anacortes"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="Seattle"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="Burlington"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="Friday Harbor"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="La Conner"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="Bellingham"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="Everett"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
						<LocationItem
							city="Lynden"
							state="Washington"
							country="United States"
							flag="flag.png"
							count="4 Donations"
							total="$345.00"
						/>
					</List>
				</Card>
				<Card width={ 4 }>
					<RESTList
						title={ __( 'Recent Donations', 'give' ) }
						endpoint="recent-donations"
					/>
				</Card>
			</Grid>
		</Fragment>
	);
};
export default OverviewPage;
