/* eslint-disable no-unused-vars */

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;

/* eslint-disable-next-line no-undef */
const editorColorPalette = giveProgressBarThemeSupport.editorColorPalette;

import ColorControl from '../color-control';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	// eslint-disable-next-line camelcase
	const { accent_color } = attributes;
	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};
	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Appearance', 'give' ) } initialOpen={ true }>
				<ColorControl
					colors={ editorColorPalette }
					name="color"
					label={ __( 'Accent Color', 'give' ) }
					onChange={ ( value ) => saveSetting( 'accent_color', value ) }
					// eslint-disable-next-line camelcase
					value={ accent_color }
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
