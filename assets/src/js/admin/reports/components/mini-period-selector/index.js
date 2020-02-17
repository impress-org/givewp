// Store-related dependencies
import { useStoreValue } from '../../store';

import './style.scss';
const { __ } = wp.i18n;

const MiniPeriodSelector = () => {
	// Get 'period' object from the store
	const [ { period }, dispatch ] = useStoreValue();

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
		<div className="givewp-mini-period-selector">
			<div className="group">
				<button className={ period.range === 'day' ? 'selected' : null } onClick={ () => setRange( 'day' ) }>{ __( 'Day', 'give' ) }</button>
				<button className={ period.range === 'week' ? 'selected' : null } onClick={ () => setRange( 'week' ) }>{ __( 'Week', 'give' ) }</button>
				<button className={ period.range === 'month' ? 'selected' : null } onClick={ () => setRange( 'month' ) }>{ __( 'Month', 'give' ) }</button>
			</div>
		</div>
	);
};

export default MiniPeriodSelector;
