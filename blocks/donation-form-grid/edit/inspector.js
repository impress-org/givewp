/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.editor;
const { PanelBody, SelectControl, ToggleControl } = wp.components;

/**
 * Internal dependencies
 */
import giveFormOptions from '../data/options';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { columns, showExcerpt, showGoal, showFeaturedImage, displayType } = attributes;
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
					options={ giveFormOptions.columns }
					onChange={ ( value ) => saveSetting( 'columns', value ) } />
				<ToggleControl
					name="showExcerpt"
					label={ __( 'Show Excerpt' ) }
					checked={ !! showExcerpt }
					onChange={ ( value ) => saveSetting( 'showExcerpt', value ) } />
				<ToggleControl
					name="showGoal"
					label={ __( 'Show Goal' ) }
					checked={ !! showGoal }
					onChange={ ( value ) => saveSetting( 'showGoal', value ) } />
				<ToggleControl
					name="showFeaturedImage"
					label={ __( 'Show Featured Image' ) }
					checked={ !! showFeaturedImage }
					onChange={ ( value ) => saveSetting( 'showFeaturedImage', value ) } />
				<SelectControl
					label={ __( 'Display Type' ) }
					name="displayType"
					value={ displayType }
					options={ giveFormOptions.displayType }
					onChange={ ( value ) => saveSetting( 'displayType', value ) } />
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
