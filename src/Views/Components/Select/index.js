import PropTypes from 'prop-types';
import classNames from 'classnames';

import style from './style.module.scss';

const Select = ( { options, onChange, defaultValue, className, ...rest } ) => {
	const optionElements = options.map( ( option ) =>
		<option key={ option.value } value={ option.value }>{ option.label }</option>
	);
	return (
		<div key={ defaultValue } className={ classNames( style.select, className ) }>
			<select onChange={ onChange } defaultValue={ defaultValue } { ...rest }>
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
	// Default value of select element
	defaultValue: PropTypes.string.isRequired,
	// Additional class
	className: PropTypes.string,
};
export default Select;
