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
import RESTTable from '../../../components/rest-table';
import NotFoundNotice from '../../../components/not-found-notice';
import LoadingNotice from '../../../components/loading-notice';

const OverviewPage = () => {
	// Use initLoaded from store
	const [ { donationsFound, pageLoaded } ] = useStoreValue();
	return (
		<Fragment>
			{ donationsFound === false && (
				<NotFoundNotice />
			) }
			{ pageLoaded === false && (
				<LoadingNotice />
			) }
			<Grid visible={ pageLoaded }>
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
						endpoint="total-donors"
					/>
				</Card>
				<Card width={ 3 }>
					<RESTMiniChart
						title={ __( 'Total Refunds', 'give' ) }
						endpoint="total-refunds"
					/>
				</Card>
				<Card width={ 12 }>
					<RESTTable
						title={ __( 'Income Breakdown', 'give' ) }
						endpoint="endpoint"
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
				<Card width={ 6 }>
					<RESTList
						title={ __( 'Recent Donations', 'give' ) }
						endpoint="recent-donations"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTList
						title={ __( 'Top Donors', 'give' ) }
						endpoint="top-donors"
					/>
				</Card>
			</Grid>
		</Fragment>
	);
};
export default OverviewPage;
