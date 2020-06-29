import PropTypes from 'prop-types';
import './style.scss';

const Select = ( { options, onChange, value, prefix } ) => {
	const optionElements = options.map( ( option ) =>
		<option key={ option.value } value={ option.value }>{ option.label }</option>
	);
	return (
		<div className="givewp-reports-select">
			{ prefix }
			<select onChange={ onChange } defaultValue={ value }>
				{ optionElements }
			</select>
		</div>
	);
};

Select.propTypes = {
	// Array of objects with labels and values defining select options (ex: {label: Option A, value: option-a})
	options: PropTypes.array.isRequired,
	// Fired on select change event
	onChange: PropTypes.func,
	// Value of select element
	value: PropTypes.string.isRequired,
	// Text to prepend option
	prefix: PropTypes.string,
};
export default Select;
