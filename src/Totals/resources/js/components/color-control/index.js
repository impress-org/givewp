/**
 * Vendor dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const { useInstanceId } = wp.compose;
const { BaseControl, ColorPalette } = wp.components;
const { __ } = wp.i18n;

const ImageControl = ( { name, label, help, className, value, hideLabelFromVision, onChange } ) => {
	const instanceId = useInstanceId( ImageControl );
	const id = `give-color-control-${ name }-${ instanceId }`;
	const colors = [
		{ name: __( 'Red', 'give' ), color: '#dd3333' },
		{ name: __( 'Orange', 'give' ), color: '#dd9933' },
		{ name: __( 'Green', 'give' ), color: '#28C77B' },
		{ name: __( 'Blue', 'give' ), color: '#1e73be' },
		{ name: __( 'Purple', 'give' ), color: '#8224e3' },
		{ name: __( 'Grey', 'give' ), color: '#777777' },
	];
	return (
		<BaseControl
			label={ label }
			hideLabelFromVision={ hideLabelFromVision }
			id={ id }
			help={ help }
			className={ className }
		>
			<ColorPalette
				value={ value }
				colors={ colors }
				onChange={ ( newValue ) => onChange( newValue ) }
				clearable={ false }
			/>
		</BaseControl>
	);
};

ImageControl.propTypes = {
	label: PropTypes.string,
	value: PropTypes.any.isRequired,
	onChange: PropTypes.func,
	name: PropTypes.string.isRequired,
	help: PropTypes.string,
	className: PropTypes.string,
	hideLabelFromVision: PropTypes.bool,
};

ImageControl.defaultProps = {
	onChange: null,
	options: null,
};

export default ImageControl;
