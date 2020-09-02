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
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { title, forms } = attributes;
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
				<MultiSelectControl
					name="forms"
					label={ __( 'Forms', 'give' ) }
					value={ forms }
					options={ formOptions }
					onChange={ ( value ) => saveSetting( 'forms', value ) } />
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
