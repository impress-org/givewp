/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl, ColorPalette } from '@wordpress/components';

/**
 * Internal dependencies
 */
import giveFormOptions from '../data/options';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { formsPerPage, paged, imageSize, imageHeight, formIDs, excludedFormIDs, orderBy, order, categories, tags, columns, showTitle, showExcerpt, excerptLength, showGoal, showFeaturedImage, showDonateButton, donateButtonBackgroundColor, donateButtonTextColor, displayType } = attributes;
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
                <ToggleControl
                    name="paged"
                    label={ __( 'Show Pagination', 'give' ) }
                    checked={ !! paged }
                    onChange={ ( value ) => saveSetting( 'paged', value ) }
                />
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
                <TextControl
                    name="imageSize"
                    label={ __( 'Image Size', 'give' ) }
                    value={ imageSize }
                    onChange={ ( value ) => saveSetting( 'imageSize', value ) }
                    help={ __( 'Featured image size. Default "medium". Accepts WordPress image sizes.' ) }
                />
                <TextControl
                    name="imageHeight"
                    label={ __( 'Image Height', 'give' ) }
                    value={ imageHeight }
                    onChange={ ( value ) => saveSetting( 'imageHeight', value ) }
                    help={ __( 'Featured image height. Default "auto". Accepts valid CSS heights' ) }
                />
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
                <TextControl
                    name="excerptLength"
                    label={ __( 'Excerpt Length', 'give' ) }
                    value={ excerptLength ? excerptLength : '' }
                    className={ !showExcerpt && 'hidden' }
                    onChange={ ( value ) => {
                        if( value === '' ) {
                            saveSetting('excerptLength', 0 );
                        }
                        const intValue = Math.abs( Number.parseInt(value) );
                        if( intValue == value )
                            saveSetting( 'excerptLength', value )
                    } }/>
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
                <ToggleControl
					name="showDonateButton"
					label={ __( 'Show Donate Button', 'give' ) }
					checked={ !! showDonateButton }
					onChange={ ( value ) => saveSetting( 'showDonateButton', value ) } />
                { showDonateButton && (
                    <>
                        <p>
                            { __('Donate Button Background Color', 'give' ) }
                        </p>
                        <ColorPalette
                            clearable={false}
                            onChange={( value ) => saveSetting( 'donateButtonBackgroundColor', value )}
                            value={ donateButtonBackgroundColor }
                        />
                        <p>
                            { __('Donate Button Text Color', 'give' ) }
                        </p>
                        <ColorPalette
                            clearable={false}
                            onChange={( value ) => saveSetting( 'donateButtonTextColor', value )}
                            value={ donateButtonTextColor }
                        />
                    </>
                ) }

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
