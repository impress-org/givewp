// Import vendor dependencies
import PropTypes from 'prop-types';
import Select from 'react-select';

// Import utilities
import { toKebabCase } from '../../utils';

// Import styles
import './style.scss';

const { __ } = wp.i18n;

const SelectControl = ( { label, value, isLoading, onChange, options, placeholder, width } ) => {
	if ( options && options.length < 2 ) {
		return null;
	}

	const selectedOptionValue = options !== null ? options.filter( option => option.value === value ) : null;
	const selectStyles = {
		control: ( provided ) => ( {
			...provided,
			fontSize: '14px',
			fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
			fontWeight: '500',
			color: '#828382',
			lineHeight: '1.2',
			boxSizing: 'border-box',
			marginTop: '8px',
			border: '1px solid #b8b8b8',
			borderRadius: '4px',
		} ),
		input: ( provided ) => ( {
			...provided,
			fontSize: '14px',
			fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
			fontWeight: '500',
			color: '#828382',
			lineHeight: '1.2',
		} ),
		valueContainer: ( provided ) => ( {
			...provided,
			padding: '7px 12px',
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
		<div className="give-donor-profile-select-control" style={ width ? { maxWidth: width } : null }>
			{ label && ( <label className="give-donor-profile-select-control__label" htmlFor={ toKebabCase( label ) }>{ label }</label> ) }
			<Select
				placeholder={ placeholder }
				isLoading={ isLoading }
				inputId={ label && toKebabCase( label ) }
				value={ selectedOptionValue }
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

SelectControl.propTypes = {
	label: PropTypes.string,
	value: PropTypes.string.isRequired,
	onChange: PropTypes.func,
	options: PropTypes.array.isRequired,
	placeholder: PropTypes.string,
	width: PropTypes.string,
};

SelectControl.defaultProps = {
	label: null,
	value: null,
	onChange: null,
	options: null,
	placeholder: __( 'Select...', 'give' ),
	width: null,
};

export default SelectControl;
