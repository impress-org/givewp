// Import vendor dependencies
import PropTypes from 'prop-types';
import Select from 'react-select';

// Import utilities
import { toKebabCase } from '../../utils';

// Import styles
import './style.scss';

const SelectInput = ( { label, value, onChange, options } ) => {
	const selectedOptionValue = options.filter( option => option.value === value );
	const selectStyles = {
		control: ( provided ) => ( {
			...provided,
			fontSize: '14px',
			fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
			fontWeight: '500',
			color: '#333',
			lineHeight: '1.2',
			width: '356px',
			boxSizing: 'border-box',
			marginTop: '10px',
			border: '1px solid #b8b8b8',
			boxShadow: '0 1px 4px rgba(0, 0, 0, 0.158927)',
			borderRadius: '4px',
		} ),
		input: ( provided ) => ( {
			...provided,
			fontSize: '14px',
			fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
			fontWeight: '500',
			color: '#333',
			lineHeight: '1.2',
		} ),
		valueContainer: ( provided ) => ( {
			...provided,
			padding: '13px 15px',
		} ),
		option: ( provided, state ) => ( {
			...provided,
			fontSize: '14px',
			fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
			fontWeight: '500',
			color: state.isSelected ? '#fff' : '#333',
			background: state.isSelected ? '#4fa651' : '#fff',
			lineHeight: '1.2',
		} ),
		indicatorSeparator: () => ( {
			display: 'none',
		} ),
	};

	return (
		<div className="give-obw-select-input">
			{ label && ( <label className="give-obw-select-input__label" htmlFor={ toKebabCase( label ) }>{ label }</label> ) }
			<Select
				inputId={ label && toKebabCase( label ) }
				value={ selectedOptionValue }
				onChange={ ( selectedOption ) => onChange( selectedOption.value ) }
				options={ options }
				styles={ selectStyles }
				maxMenuHeight="200px"
				isDisabled={ options.length < 2 }
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
