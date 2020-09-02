/**
 * Vendor dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const { useInstanceId } = wp.compose;
const { BaseControl, Button } = wp.components;
const { MediaUpload } = wp.blockEditor;
const { __ } = wp.i18n;

const ImageControl = ( { name, label, help, className, value, hideLabelFromVision, onChange } ) => {
	const instanceId = useInstanceId( ImageControl );
	const id = `give-image-control-${ name }-${ instanceId }`;
	return (
		<BaseControl
			label={ label }
			hideLabelFromVision={ hideLabelFromVision }
			id={ id }
			help={ help }
			className={ className }
		>
			<MediaUpload
				allowedTypes={ [ 'image' ] }
				onSelect={ ( media ) => onChange( media.sizes.full.url ) }
				render={ ( { open } ) => {
					return value ? (
						<div>
							<img src={ value } onClick={ open } style={ { cursor: 'pointer' } } />
							<Button isPrimary isSmall onClick={ open } id={ id }>
								{ __( 'Change Image', 'give' ) }
							</Button>
							<Button isSecondary isSmall onClick={ () => onChange( '' ) } id={ id }>
								{ __( 'Remove Image', 'give' ) }
							</Button>
						</div>
					) : (
						<div>
							<Button isPrimary onClick={ open } id={ id }>
								{ __( 'Select an Image', 'give' ) }
							</Button>
						</div>
					);
				} }
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
