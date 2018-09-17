/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.editor;
const { PanelBody, SelectControl, ToggleControl } = wp.components;

/**
 * Internal dependencies
 */
import giveDonorWallOptions from '../data/options';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { columns, showAvatar, showName, showTotal, showDate, showComments } = attributes;
	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};

	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Settings' ) }>
				<SelectControl
					label={ __( 'Columns' ) }
					name="columns"
					value={ columns }
					options={ giveDonorWallOptions.columns }
					onChange={ ( value ) => saveSetting( 'columns', value ) } />
				<ToggleControl
					name="showAvatar"
					label={ __( 'Show Avatar' ) }
					checked={ !! showAvatar }
					onChange={ ( value ) => saveSetting( 'showAvatar', value ) } />
				<ToggleControl
					name="showName"
					label={ __( 'Show Name' ) }
					checked={ !! showName }
					onChange={ ( value ) => saveSetting( 'showName', value ) } />
				<ToggleControl
					name="showTotal"
					label={ __( 'Show Total' ) }
					checked={ !! showTotal }
					onChange={ ( value ) => saveSetting( 'showTotal', value ) } />
				<ToggleControl
					name="showDate"
					label={ __( 'Show Time' ) }
					checked={ !! showDate }
					onChange={ ( value ) => saveSetting( 'showDate', value ) } />
				<ToggleControl
					name="showComments"
					label={ __( 'Show Comments' ) }
					checked={ !! showComments }
					onChange={ ( value ) => saveSetting( 'showComments', value ) } />
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
