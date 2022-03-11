// Store-related dependencies
import { useStoreValue } from '../../store';
import { setRange } from '../../store/actions';
import './style.scss';
import { __ } from '@wordpress/i18n';

const MiniPeriodSelector = () => {
	// Get 'period' object from the store
	const [ { period }, dispatch ] = useStoreValue();

	return (
		<div className="givewp-mini-period-selector">
			<div className="group">
				<button className={ period.range === 'day' ? 'selected' : null } onClick={ () => dispatch( setRange( 'day' ) ) }>{ __( 'Day', 'give' ) }</button>
				<button className={ period.range === 'week' ? 'selected' : null } onClick={ () => dispatch( setRange( 'week' ) ) }>{ __( 'Week', 'give' ) }</button>
				<button className={ period.range === 'month' ? 'selected' : null } onClick={ () => dispatch( setRange( 'month' ) ) }>{ __( 'Month', 'give' ) }</button>
			</div>
		</div>
	);
};

export default MiniPeriodSelector;
