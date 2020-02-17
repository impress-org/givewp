// Dependencies
const { __ } = wp.i18n;

// Store-related dependencies
import { useStoreValue } from '../../store';

// Styles
import './style.scss';

const NotFoundNotice = ( { range } ) => {
	// Get 'period' object from the store
	const [ {}, dispatch ] = useStoreValue();

	// Dispatch SET_RANGE action
	const setRange = ( noticeRange ) => {
		dispatch( {
			type: 'SET_RANGE',
			payload: {
				range: noticeRange,
			},
		} );
	};

	let label;
	switch ( range ) {
		case 'month':
			label = __( 'See Donations for Month', 'give' );
			break;
		case 'alltime':
			label = __( 'See All Time Donations', 'give' );
			break;
	}

	return (
		<div className="givewp-not-found-notice">
			<div className="givewp-not-found-card">
				<h2>{ __( 'Get a quick read of your', 'give' ) }<br />{ __( 'GiveWP Donations', 'give' ) }</h2>
				<p>
					{ __( 'Uh oh! Looks like we can\'t find any donations.', 'give' ) } <br />
					{ __( 'Try looking at a different stretch of time, or use the Reports page to view all-time records.', 'give' ) } <br />
				</p>
				<button
					onClick={ () => setRange( range ) }
					className="givewp-not-found-notice-button">
					{ label }
				</button>
			</div>
		</div>
	);
};

NotFoundNotice.defaultProps = {
	range: 'alltime',
};

export default NotFoundNotice;
