/* eslint-disable no-unused-vars */

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { enabled } = attributes;
	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};
	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Tabs', 'give' ) } initialOpen={ true }>
				<h2>{ __( 'This is a test', 'give' ) }</h2>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
