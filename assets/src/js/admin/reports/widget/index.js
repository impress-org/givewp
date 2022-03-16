// Reports admin dashboard widget

// Dependencies
import { __ } from '@wordpress/i18n'

// Store-related dependencies
import { useStoreValue } from '../store';

import './style.scss';

// Components
import Grid from '../components/grid';
import Card from '../components/card';
import RESTChart from '../components/rest-chart';
import RESTMiniChart from '../components/rest-mini-chart';
import NoDataNotice from '../components/no-data-notice';
import LoadingNotice from '../components/loading-notice';
import MiniPeriodSelector from '../components/mini-period-selector';

const Widget = () => {
	const [ { giveStatus, pageLoaded } ] = useStoreValue();

	return (
		<div className="givewp-reports-widget-container">
			{ giveStatus === 'no_donations_found' && (
				<NoDataNotice version={ 'dashboard' } />
			) }
			{ pageLoaded === false && (
				<LoadingNotice />
			) }
			<Grid gap="12px" visible={ pageLoaded }>
				<Card width={ 12 }>
					<RESTChart
						title={ __( 'Overview', 'give' ) }
						headerElements={ <MiniPeriodSelector /> }
						type="line"
						aspectRatio={ 0.8 }
						endpoint="income"
						showLegend={ false }
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Total Revenue', 'give' ) }
						endpoint="total-income"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Avg. Donation', 'give' ) }
						endpoint="average-donation"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Total Donors', 'give' ) }
						endpoint="total-donors"
					/>
				</Card>
				<Card width={ 6 }>
					<RESTMiniChart
						title={ __( 'Total Refunds', 'give' ) }
						endpoint="total-refunds"
					/>
				</Card>
			</Grid>
		</div>
	);
};
export default Widget;
