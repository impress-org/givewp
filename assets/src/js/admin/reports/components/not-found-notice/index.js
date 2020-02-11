// Dependencies
const { __ } = wp.i18n;

// Store-related dependencies
import { useStoreValue } from '../../store';

// Styles
import './style.scss';

const NotFoundNotice = () => {
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
			<h2>{ __( 'No donations were found for this period.', 'give' ) }</h2>
			<button
				onClick={ () => setRange( 'alltime' ) }
				className="givewp-not-found-notice-button">
				{ __( 'See All Time Donations', 'give' ) }
			</button>
		</div>
	);
};

export default NotFoundNotice;
