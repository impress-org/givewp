// Dependencies
const { __ } = wp.i18n;

// Store-related dependencies
import { useStoreValue } from '../../store';

// Styles
import './style.scss';

const NotFoundNotice = ( { noticeRange } ) => {
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

	let label;
	switch ( noticeRange ) {
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
				<h2>{ __( 'No donations were found for this period.', 'give' ) }</h2>
				<button
					onClick={ () => setRange( noticeRange ) }
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
