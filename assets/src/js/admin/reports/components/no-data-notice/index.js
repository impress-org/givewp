// Dependencies
import { useState, Fragment } from 'react';
const { __ } = wp.i18n;

// Store-related dependencies
import { useStoreValue } from '../../store';

// Styles
import './style.scss';

const NoDataNotice = ( { version } ) => {
	// Get 'period' object from the store
	const [ {}, dispatch ] = useStoreValue();

	// Dispatch SET_RANGE action
	const disablePeriodSelector = () => {
		dispatch( {
			type: 'ENABLE_PERIOD_SELECTOR',
			payload: false,
		} );
	};

	const [ showNotice, setShowNotice ] = useState( true );

	const loadSampleData = () => {
		setShowNotice( false );
		disablePeriodSelector();
	};

	const goToNewFormUrl = () => {
		window.location = window.giveReportsData.newFormUrl;
	};

	return (
		<Fragment>
			{ showNotice && (
				<div className="givewp-not-found-notice">
					<div className="givewp-not-found-card">
						{ version === 'dashboard' ? (
							<Fragment>
								<h2>{ __( 'Get a quick view of your', 'give' ) }<br />{ __( 'GiveWP Donations', 'give' ) }</h2>
								<p>
									{ __( 'Uh oh! Looks like we can\'t find any donations.', 'give' ) } <br />
									{ __( 'Setup a donation form, or visit the Reports page to explore some sample data.', 'give' ) } <br />
								</p>
								<button
									onClick={ () => goToNewFormUrl() }
									className="givewp-not-found-notice-button">
									{ __( 'Create a Donation Form', 'give' ) }
								</button>
							</Fragment>
						) : (
							<Fragment>
								<h2>{ __( 'Get a detailed view of your', 'give' ) }<br />{ __( 'GiveWP Donations', 'give' ) }</h2>
								<p>
									{ __( 'Uh oh! Looks like we can\'t find any donations.', 'give' ) } <br />
									{ __( 'Setup a donation form, or load some sample data with the button below.', 'give' ) } <br />
								</p>
								<button
									onClick={ () => loadSampleData() }
									className="givewp-not-found-notice-button">
									{ __( 'Explore Sample Reports', 'give' ) }
								</button>
							</Fragment>
						) }
					</div>
				</div>
			) }
		</Fragment>
	);
};

NoDataNotice.defaultProps = {
	version: 'app',
};

export default NoDataNotice;
