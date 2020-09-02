/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, TextControl, Button } = wp.components;

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { title, image } = attributes;
	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};

	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Milestone Settings', 'give' ) }>
				<TextControl
					name="title"
					label={ __( 'Title', 'give' ) }
					value={ title }
					onChange={ ( value ) => saveSetting( 'title', value ) } />
				<MediaUpload
					onSelect={ ( value ) => saveSetting( 'image', value.sizes.full.url ) }
					render={ ( { open } ) => {
						return image ? (
							<img
								src={ image }
								onClick={ open }
							/>
						) : (
							<Button onClick={ open }>
								Set Milestone Image
							</Button>
						);
					} }
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
