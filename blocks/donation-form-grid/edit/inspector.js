/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.editor;
const { PanelBody } = wp.components;

/**
 * Internal dependencies
 */
import GiveToggleControl from '../../components/toggle-control/index';
import GiveSelectControl from '../../components/select-control/index';
import giveFormOptions from '../data/options';

/**
 * Render Inspector Controls
*/

const Inspector = ( { attributes, setAttributes } ) => {
	const { columns, showExcerpt, showGoal, showFeaturedImage, displayType } = attributes;
	const saveSetting = ( event ) => {
		const name = event.target.name;

		setAttributes(
			'checkbox' === event.target.type ?
				{ [ name ]: ! attributes[ name ] } :
				{ [ name ]: event.target.value }
		);
	};

	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Settings' ) }>
				<GiveSelectControl
					label={ __( 'Columns' ) }
					name="columns"
					value={ columns }
					options={ giveFormOptions.columns }
					onChange={ saveSetting } />
				<GiveToggleControl
					name="showExcerpt"
					label={ __( 'Show Excerpt' ) }
					checked={ !! showExcerpt }
					onChange={ saveSetting } />
				<GiveToggleControl
					name="showGoal"
					label={ __( 'Show Goal' ) }
					checked={ !! showGoal }
					onChange={ saveSetting } />
				<GiveToggleControl
					name="showFeaturedImage"
					label={ __( 'Show Featured Image' ) }
					checked={ !! showFeaturedImage }
					onChange={ saveSetting } />
				<GiveSelectControl
					label={ __( 'Display Type' ) }
					name="displayType"
					value={ displayType }
					options={ giveFormOptions.displayType }
					onChange={ saveSetting } />
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
