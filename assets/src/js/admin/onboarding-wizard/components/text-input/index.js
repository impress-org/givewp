// Import vendor dependencies
import PropTypes from 'prop-types';
import Select from 'react-select';

// Import utilities
import { toKebabCase } from '../../utils';

// Import styles
import './style.scss';

const SelectInput = ( { label, value, testId, isLoading, onChange, options } ) => {
	if ( options && options.length < 2 ) {
		return null;
	}

	const selectedOptionValue = options !== null ? options.filter( option => option.value === value ) : null;
	const selectStyles = {
		control: ( provided, state ) => ( {
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
			boxShadow: state.isFocused ? 	'0 0 0 2px #7ec980, 0 0 0 3px #4fa651' : '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
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
			lineHeight: '1.2',
		} ),
		indicatorSeparator: () => ( {
			display: 'none',
		} ),
	};

	return (
		<div className="give-obw-select-input" data-givewp-test={ testId }>
			{ label && ( <label className="give-obw-select-input__label" htmlFor={ toKebabCase( label ) }>{ label }</label> ) }
			<Select
				isLoading={ isLoading }
				inputId={ label && toKebabCase( label ) }
				value={ selectedOptionValue }
				classNamePrefix="givewp-select"
				onChange={ ( selectedOption ) => onChange( selectedOption.value ) }
				options={ options }
				styles={ selectStyles }
				maxMenuHeight="200px"
				isDisabled={ isLoading }
				theme={ ( theme ) => ( {
					...theme,
					colors: {
						...theme.colors,
						primary: '#4fa651',
						primary75: '#77b579',
						primary50: '#c5e0c7',
						primary25: '#e6f5e7',
					},
				} ) }
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
