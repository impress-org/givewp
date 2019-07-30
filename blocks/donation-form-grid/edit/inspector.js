/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, ToggleControl, TextControl } = wp.components;

/**
 * Internal dependencies
 */
import giveFormOptions from '../data/options';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { formsPerPage, formIDs, excludedFormIDs, orderBy, order, categories, tags, columns, showTitle, showExcerpt, showGoal, showFeaturedImage, displayType } = attributes;
	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};

	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Form Grid Settings' ) }>
				<TextControl
					name="formsPerPage"
					label={ __( 'Forms Per Page' ) }
					value={ formsPerPage }
					onChange={ ( value ) => saveSetting( 'formsPerPage', value ) }/>
				<TextControl
					name="formIDs"
					label={ __( 'Form IDs' ) }
					value={ formIDs }
					onChange={ ( value ) => saveSetting( 'formIDs', value ) }/>
				<TextControl
					name="excludedFormIDs"
					label={ __( 'Excluded Form IDs' ) }
					value={ excludedFormIDs }
					onChange={ ( value ) => saveSetting( 'excludedFormIDs', value ) }/>
				<SelectControl
					label={ __( 'Order By' ) }
					name="orderBy"
					value={ orderBy }
					options={ giveFormOptions.orderBy }
					onChange={ ( value ) => saveSetting( 'orderBy', value ) } />
				<SelectControl
					label={ __( 'Order' ) }
					name="order"
					value={ order }
					options={ giveFormOptions.order }
					onChange={ ( value ) => saveSetting( 'order', value ) } />
				<TextControl
					name="categories"
					label={ __( 'Categories' ) }
					value={ categories }
					onChange={ ( value ) => saveSetting( 'categories', value ) }/>
				<TextControl
					name="tags"
					label={ __( 'Tags' ) }
					value={ tags }
					onChange={ ( value ) => saveSetting( 'tags', value ) }/>
				<SelectControl
					label={ __( 'Columns' ) }
					name="columns"
					value={ columns }
					options={ giveFormOptions.columns }
					onChange={ ( value ) => saveSetting( 'columns', value ) } />
				<ToggleControl
					name="showTitle"
					label={ __( 'Show Title' ) }
					checked={ !! showTitle }
					onChange={ ( value ) => saveSetting( 'showTitle', value ) } />
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
