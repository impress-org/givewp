/**
 * Block dependencies
 */
import giveFormOptions from '../data/options';

 /**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const {
	InspectorControls,
} = wp.blocks;
const {
	ToggleControl,
	SelectControl,
	TextControl,
} = InspectorControls;
const {
	PanelBody,
	Spinner,
} = wp.components;
const { Component } = wp.element;

/**
 * Create Inspector Wrapper Component
*/
const Inspector = ( props ) => {

	return(
	<InspectorControls key="inspector">
		<PanelBody title={ __( 'Display' ) }>
			<SelectControl
				label={ __( 'Form Format' ) }
				value={ props.attributes.displayStyle }
				options={ giveFormOptions.displayStyles }
				onChange={ props.setDisplayStyleTo } />
			{
				'reveal' === props.attributes.displayStyle && (
					<TextControl
						label={ __( 'Continue Button Title' ) }
						value={ props.attributes.continueButtonTitle }
						onChange={ props.setContinueButtonTitle }
						onBlur={ props.updateContinueButtonTitle } />
				)
			}
		</PanelBody>
		<PanelBody title={ __( 'Settings' ) }>
			<ToggleControl
				label={ __( 'Title' ) }
				checked={ !!props.attributes.showTitle }
				onChange={ props.toggleShowTitle } />
			<ToggleControl
				label={ __( 'Goal' ) }
				checked={ !!props.attributes.showGoal }
				onChange={ props.toggleShowGoal } />
			<ToggleControl
				label={ __( 'Content' ) }
				checked={ !!props.attributes.contentDisplay }
					onChange={ props.toggleContentDisplay } />
			{
				props.attributes.contentDisplay && (
					<SelectControl
						label={__('Content Position')}
						value={ props.attributes.showContent }
						options={ giveFormOptions.contentPosition }
						onChange={ props.setShowContentPosition } />
				)
			}
		</PanelBody>
	</InspectorControls>
	)
};

export default Inspector;
