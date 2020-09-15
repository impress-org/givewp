/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, TextareaControl, ToggleControl } = wp.components;
const { useState } = wp.element;

/**
 * Internal dependencies
 */

import MultiSelectControl from '../components/multi-select-control';
import { useFormOptions, useTagOptions, useCategoryOptions } from '../data/utils';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { description, ids, categories, tags, metric, goal, deadline, linkText, linkUrl, linkTarget } = attributes;
	const formOptions = useFormOptions();
	const tagOptions = useTagOptions();
	const categoryOptions = useCategoryOptions();
	const saveSetting = ( name, value ) => {
		setAttributes( {
			[ name ]: value,
		} );
	};
	const [ openInNewTab, setOpenInNewTab ] = useState( linkTarget === '_self' ? false : true );
	const toggleLinkTarget = ( value ) => {
		setOpenInNewTab( value );
		if ( value === true ) {
			saveSetting( 'linkTarget', '_blank' );
		} else {
			saveSetting( 'linkTarget', '_self' );
		}
	};
	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Milestone Settings', 'give' ) }>
				<TextareaControl
					name="description"
					label={ __( 'Description', 'give' ) }
					value={ description }
					onChange={ ( value ) => saveSetting( 'description', value ) } />
				<MultiSelectControl
					name="ids"
					label={ __( 'Forms', 'give' ) }
					value={ formOptions.filter( option => ids.includes( option.value ) ) }
					options={ formOptions }
					onChange={ ( value ) => saveSetting( 'ids', value ? value.map( ( option ) => option.value ) : [] ) } />
				<MultiSelectControl
					name="tags"
					label={ __( 'Filter by Tags', 'give' ) }
					value={ tagOptions.filter( option => tags.includes( option.value ) ) }
					options={ tagOptions }
					onChange={ ( value ) => saveSetting( 'tags', value ? value.map( ( option ) => option.value ) : [] ) } />
				<MultiSelectControl
					name="categories"
					label={ __( 'Filter by Categories', 'give' ) }
					value={ categoryOptions.filter( option => categories.includes( option.value ) ) }
					options={ categoryOptions }
					onChange={ ( value ) => saveSetting( 'categories', value ? value.map( ( option ) => option.value ) : [] ) } />
				<SelectControl
					label={ __( 'Metric', 'give' ) }
					value={ metric }
					options={ [
						{ label: __( 'Revenue', 'give' ), value: 'revenue' },
						{ label: __( 'Number of Donors', 'give' ), value: 'donor-count' },
						{ label: __( 'Number of Donations', 'give' ), value: 'donation-count' },
					] }
					onChange={ ( value ) => saveSetting( 'metric', value ) }
				/>
				<TextControl
					name="goal"
					label={ __( 'Goal', 'give' ) }
					type="number"
					onChange={ ( value ) => saveSetting( 'goal', value ) }
					value={ goal }
				/>
				<TextControl
					name="deadline"
					label={ __( 'Deadline', 'give' ) }
					type="date"
					value={ deadline }
					onChange={ ( value ) => saveSetting( 'deadline', value ) }
				/>
				<TextControl
					name="linkText"
					label={ __( 'Link Text', 'give' ) }
					onChange={ ( value ) => saveSetting( 'linkText', value ) }
					value={ linkText }
				/>
				<TextControl
					name="linkUrl"
					type="url"
					label={ __( 'Link URL', 'give' ) }
					onChange={ ( value ) => saveSetting( 'linkUrl', value ) }
					value={ linkUrl }
				/>
				<ToggleControl
					name="linkTarget"
					label={ __( 'Open link in a new tab?', 'give' ) }
					onChange={ ( value ) => toggleLinkTarget( value ) }
					checked={ openInNewTab }
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
