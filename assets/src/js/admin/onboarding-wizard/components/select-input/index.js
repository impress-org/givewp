// Import vendor dependencies
import PropTypes from 'prop-types';
import Select from 'react-select';

// Import utilities
import { toKebabCase } from '../../utils';

// Import styles
import './style.scss';

const SelectInput = ( { label, value, onChange, options } ) => {
	return (
		<div className="give-obw-select-input">
			{ label && ( <label className="give-obw-select-input__label" htmlFor={ toKebabCase( label ) }>{ label }</label> ) }
			<Select
				inputId={ label && toKebabCase( label ) }
				value={ value }
				onChange={ ( selectedOption ) => onChange( selectedOption ) }
				options={ options }
			/>
		</div>
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
