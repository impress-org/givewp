/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;
const { useSelect } = wp.data;

/**
 * Internal Dependencies
 */
import MultiSelectControl from '../../components/multi-select-control';

/**
 * Internal dependencies
 */

import ImageControl from '../components/image-control';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { title, description, image, ids } = attributes;
	const formOptions = useSelect( ( select ) => {
		const records = select( 'core' ).getEntityRecords( 'postType', 'give_forms' );
		if ( records ) {
			return records.map( ( record ) => {
				return {
					label: record.title.rendered ? record.title.rendered : __( '(no title)' ),
					value: record.id,
				};
			} );
		}
		return [];
	}, [] );
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
				<TextControl
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
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
