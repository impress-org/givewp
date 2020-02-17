// Dependencies
import { Fragment } from 'react';
const { __ } = wp.i18n;

// Store-related dependencies
import { useStoreValue } from '../../store';

// Styles
import './style.scss';

const NotFoundNotice = ( { version } ) => {
	// Get 'period' object from the store
	const [ {}, dispatch ] = useStoreValue();

	// Dispatch SET_RANGE action
	const setRange = ( range ) => {
		dispatch( {
			type: 'SET_RANGE',
			payload: {
				range,
			},
		} );
	};

	return (
		<div className="givewp-not-found-notice">
			<div className="givewp-not-found-card">
				{ version === 'dashboard' ? (
					<Fragment>
						<h2>{ __( 'Get a quick view of your', 'give' ) }<br />{ __( 'GiveWP Donations', 'give' ) }</h2>
						<p>
							{ __( 'Uh oh! Looks like we can\'t find any donations.', 'give' ) } <br />
							{ __( 'Try looking at a different stretch of time, or use the Reports page to view all-time records.', 'give' ) } <br />
						</p>
						<button
							onClick={ () => setRange( 'month' ) }
							className="givewp-not-found-notice-button">
							{ __( 'See Donations for Month', 'give' ) }
						</button>
					</Fragment>
				) : (
					<Fragment>
						<h2>{ __( 'Get a detailed view of your', 'give' ) }<br />{ __( 'GiveWP Donations', 'give' ) }</h2>
						<p>
							{ __( 'Uh oh! Looks like we can\'t find any donations.', 'give' ) } <br />
							{ __( 'Try looking at a different stretch of time, or use the button below to view all-time records.', 'give' ) } <br />
						</p>
						<button
							onClick={ () => setRange( 'alltime' ) }
							className="givewp-not-found-notice-button">
							{ __( 'See All-Time Donations', 'give' ) }
						</button>
					</Fragment>
				) }
			</div>
		</div>
	);
};

NotFoundNotice.defaultProps = {
	version: 'app',
};

export default NotFoundNotice;
