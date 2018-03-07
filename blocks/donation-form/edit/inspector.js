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
} = wp.components;
const { PanelBody } = wp.components;

/**
 * Render Inspector Controls
*/

const Inspector = ( props ) => {
	const attributes = props.attributes;

	// Event(s)
	const setDisplayStyleTo = displayStyle => {
		props.setAttributes( { displayStyle } );
	};

	const toggleShowTitle = () => {
		props.setAttributes( { showTitle: ! attributes.showTitle } );
	};

	const toggleShowGoal = () => {
		props.setAttributes( { showGoal: ! attributes.showGoal } );
	};

	const toggleContentDisplay = () => {
		props.setAttributes( { contentDisplay: ! attributes.contentDisplay } );

		// Set form Content Display Position
		if ( ! attributes.contentDisplay ) {
			props.setAttributes( { showContent: 'above' } ); // true && above
		} else if ( !! attributes.contentDisplay ) {
			props.setAttributes( { showContent: 'none' } ); // false && none
		}
	};

	const setShowContentPosition = position => {
		props.setAttributes( { showContent: position } );
	};

	const setContinueButtonTitle = continueButtonTitle => {
		props.setAttributes( { continueButtonTitle } );
		if ( ! props.isButtonTitleUpdated ) {
			props.updateButtonTitle( true );
		}
	};

	const updateContinueButtonTitle = () => {
		if ( props ) {
			props.doServerSideRender();
			props.updateButtonTitle( false );
		}
	};

	return (
		<InspectorControls key="inspector">
			<PanelBody title={ __( 'Display' ) }>
				<SelectControl
					label={ __( 'Form Format' ) }
					value={ props.attributes.displayStyle }
					options={ giveFormOptions.displayStyles }
					onChange={ setDisplayStyleTo } />
				{
					'reveal' === props.attributes.displayStyle && (
						<TextControl
							label={ __( 'Continue Button Title' ) }
							value={ props.attributes.continueButtonTitle }
							onChange={ setContinueButtonTitle }
							onBlur={ updateContinueButtonTitle } />
					)
				}
			</PanelBody>
			<PanelBody title={ __( 'Settings' ) }>
				<ToggleControl
					label={ __( 'Title' ) }
					checked={ !! props.attributes.showTitle }
					onChange={ toggleShowTitle } />
				<ToggleControl
					label={ __( 'Goal' ) }
					checked={ !! props.attributes.showGoal }
					onChange={ toggleShowGoal } />
				<ToggleControl
					label={ __( 'Content' ) }
					checked={ !! props.attributes.contentDisplay }
					onChange={ toggleContentDisplay } />
				{
					props.attributes.contentDisplay && (
						<SelectControl
							label={ __( 'Content Position' ) }
							value={ props.attributes.showContent }
							options={ giveFormOptions.contentPosition }
							onChange={ setShowContentPosition } />
					)
				}
			</PanelBody>
		</InspectorControls>
	);
};

export default Inspector;
