/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, SelectControl, TextareaControl } = wp.components;

/**
 * Internal dependencies
 */

import ImageControl from '../components/image-control';
import MultiSelectControl from '../components/multi-select-control';
import { useFormOptions, useTagOptions, useCategoryOptions } from '../data/utils';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { title, description, image, ids, categories, tags, metric, goal, deadline, link } = attributes;
	const formOptions = useFormOptions();
	const tagOptions = useTagOptions();
	const categoryOptions = useCategoryOptions();
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
				<TextareaControl
					name="description"
					label={ __( 'Description', 'give' ) }
					value={ description }
					onChange={ ( value ) => saveSetting( 'description', value ) } />
				<ImageControl
					name="image"
					label={ __( 'Featured Image', 'give' ) }
					value={ image }
					onChange={ ( value ) => saveSetting( 'image', value ) } />
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
					name="link"
					type="url"
					label={ __( 'Custom Link', 'give' ) }
					onChange={ ( value ) => saveSetting( 'link', value ) }
					value={ link }
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
