/**
 * Vendor dependencies
 */
import PropTypes from 'prop-types';
import Select from 'react-select';

/**
 * WordPress dependencies
 */
const { useInstanceId } = wp.compose;
const { BaseControl } = wp.components;

/**
 * Styles
 */
import './style.scss';

const MultiSelectControl = ( { name, label, help, className, value, hideLabelFromVision, isLoading, isDisabled, onChange, options } ) => {
	const instanceId = useInstanceId( MultiSelectControl );
	const id = `give-multi-select-control-${ name }-${ instanceId }`;

	if ( options && options.length < 1 ) {
		return null;
	}
	return (
		<BaseControl
			label={ label }
			hideLabelFromVision={ hideLabelFromVision }
			id={ id }
			help={ help }
			className={ className }
		>
			<Select
				isLoading={ isLoading }
				inputId={ id }
				value={ value }
				onChange={ ( selectedOptions ) => onChange( selectedOptions ) }
				options={ options }
				maxMenuHeight="200px"
				isDisabled={ isDisabled }
				isMulti={ true }
				theme={ ( theme ) => ( {
					...theme,
					colors: {
						...theme.colors,
						primary: '#007cba',
						primary75: '#31a6e0',
						primary50: '#5dbae8',
						primary25: '#9edaf7',
					},
				} ) }
			/>
		</BaseControl>
	);
};

MultiSelectControl.propTypes = {
	label: PropTypes.string,
	value: PropTypes.any.isRequired,
	onChange: PropTypes.func,
	options: PropTypes.array.isRequired,
	name: PropTypes.string.isRequired,
	help: PropTypes.string,
	className: PropTypes.string,
	hideLabelFromVision: PropTypes.bool,
	isLoading: PropTypes.bool,
	isDisabled: PropTypes.bool,
};

MultiSelectControl.defaultProps = {
	label: null,
	value: null,
	onChange: null,
	options: null,
};

export default MultiSelectControl;
