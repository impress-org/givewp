// Import vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const SelectInput = ( { label, value, onChange, options } ) => {
	const optionElements = options.map( ( option, index ) => {
		return (
			<option value={ option.value } key={ index }>{ option.label }</option>
		);
	} );
	return (
		<Fragment>
			{ label && <p>{ label }</p> }
			<select value={ value } className="give-obw-select-input" onChange={ ( event ) => onChange( event.target.val ) } >
				{ optionElements }
			</select>
		</Fragment>
	);
};

SelectInput.propTypes = {
	label: PropTypes.string,
	value: PropTypes.string.isRequired,
	onChange: PropTypes.func,
	options: PropTypes.array.isRequired,
};

SelectInput.defaultProps = {
	label: null,
	value: null,
	onChange: null,
	options: null,
};

export default SelectInput;
