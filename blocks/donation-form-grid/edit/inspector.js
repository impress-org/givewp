/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl } from '@wordpress/components';

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
			<PanelBody title={ __( 'Form Grid Settings', 'give' ) }>
				<TextControl
					name="formsPerPage"
					label={ __( 'Forms Per Page', 'give' ) }
					value={ formsPerPage }
					onChange={ ( value ) => saveSetting( 'formsPerPage', value ) }/>
				<TextControl
					name="formIDs"
					label={ __( 'Form IDs', 'give' ) }
					value={ formIDs }
					onChange={ ( value ) => saveSetting( 'formIDs', value ) }/>
				<TextControl
					name="excludedFormIDs"
					label={ __( 'Excluded Form IDs', 'give' ) }
					value={ excludedFormIDs }
					onChange={ ( value ) => saveSetting( 'excludedFormIDs', value ) }/>
				<SelectControl
					label={ __( 'Order By', 'give' ) }
					name="orderBy"
					value={ orderBy }
					options={ giveFormOptions.orderBy }
					onChange={ ( value ) => saveSetting( 'orderBy', value ) } />
				<SelectControl
					label={ __( 'Order', 'give' ) }
					name="order"
					value={ order }
					options={ giveFormOptions.order }
					onChange={ ( value ) => saveSetting( 'order', value ) } />
				<TextControl
					name="categories"
					label={ __( 'Categories', 'give' ) }
					value={ categories }
					onChange={ ( value ) => saveSetting( 'categories', value ) }/>
				<TextControl
					name="tags"
					label={ __( 'Tags', 'give' ) }
					value={ tags }
					onChange={ ( value ) => saveSetting( 'tags', value ) }/>
				<SelectControl
					label={ __( 'Columns', 'give' ) }
					name="columns"
					value={ columns }
					options={ giveFormOptions.columns }
					onChange={ ( value ) => saveSetting( 'columns', value ) } />
				<ToggleControl
					name="showTitle"
					label={ __( 'Show Title', 'give' ) }
					checked={ !! showTitle }
					onChange={ ( value ) => saveSetting( 'showTitle', value ) } />
				<ToggleControl
					name="showExcerpt"
					label={ __( 'Show Excerpt', 'give' ) }
					checked={ !! showExcerpt }
					onChange={ ( value ) => saveSetting( 'showExcerpt', value ) } />
				<ToggleControl
					name="showGoal"
					label={ __( 'Show Goal', 'give' ) }
					checked={ !! showGoal }
					onChange={ ( value ) => saveSetting( 'showGoal', value ) } />
				<ToggleControl
					name="showFeaturedImage"
					label={ __( 'Show Featured Image', 'give' ) }
					checked={ !! showFeaturedImage }
					onChange={ ( value ) => saveSetting( 'showFeaturedImage', value ) } />
				<SelectControl
					label={ __( 'Display Type', 'give' ) }
					name="displayType"
					value={ displayType }
					options={ giveFormOptions.displayType }
					onChange={ ( value ) => saveSetting( 'displayType', value ) } />
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
