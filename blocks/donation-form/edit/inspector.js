/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { Dashicon, Button, PanelBody, SelectControl, ToggleControl, TextControl } = wp.components;
const { Component } = wp.element;
const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
import giveFormOptions from '../data/options';
import { isLegacyForm } from '../../utils';

/**
 * Render Inspector Controls
*/
class Inspector extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			continueButtonTitle: this.props.attributes.continueButtonTitle,
		};

		this.saveSetting = this.saveSetting.bind( this );
		this.saveState = this.saveState.bind( this );
	}

	saveSetting( name, value ) {
		this.props.setAttributes( {
			[ name ]: value,
		} );
	}

	saveState( name, value ) {
		this.setState( {
			[ name ]: value,
		} );
	}

	render() {
		const { forms } = this.props;

		const {
			id,
			displayStyle,
			showTitle,
			showGoal,
			showContent,
			contentDisplay,
		} = this.props.attributes;

		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Donation Form Settings' ) }>
					<Button isDefault onClick={ () => this.saveSetting( 'id', 0 ) } className={ 'give-change-donation-form-btn' }>
						<Dashicon icon="edit" /> { __( 'Change Donation Form' ) }
					</Button>
				</PanelBody>
				{
					isLegacyForm( forms, id ) && (
						<div>
							<PanelBody title={ __( 'Display' ) }>
								<SelectControl
									label={ __( 'Form Format' ) }
									name="displayStyle"
									value={ displayStyle }
									options={ giveFormOptions.displayStyles }
									onChange={ ( value ) => this.saveSetting( 'displayStyle', value ) } />
								{
									'reveal' === displayStyle && (
										<TextControl
											name="continueButtonTitle"
											label={ __( 'Continue Button Title' ) }
											value={ this.state.continueButtonTitle }
											onChange={ ( value ) => this.saveState( 'continueButtonTitle', value ) }
											onBlur={ ( event ) => this.saveSetting( 'continueButtonTitle', event.target.value ) } />
									)
								}
							</PanelBody>
							<PanelBody title={ __( 'Settings' ) }>
								<ToggleControl
									label={ __( 'Title' ) }
									name="showTitle"
									checked={ !! showTitle }
									onChange={ ( value ) => this.saveSetting( 'showTitle', value ) } />
								<ToggleControl
									label={ __( 'Goal' ) }
									name="showGoal"
									checked={ !! showGoal }
									onChange={ ( value ) => this.saveSetting( 'showGoal', value ) } />
								<ToggleControl
									label={ __( 'Content' ) }
									name="contentDisplay"
									checked={ !! contentDisplay }
									onChange={ ( value ) => this.saveSetting( 'contentDisplay', value ) } />
								{
									contentDisplay && (
										<SelectControl
											label={ __( 'Content Position' ) }
											name="showContent"
											value={ showContent }
											options={ giveFormOptions.contentPosition }
											onChange={ ( value ) => this.saveSetting( 'showContent', value ) } />
									)
								}
							</PanelBody>
						</div>
					) }
			</InspectorControls>
		);
	}
}

/**
 * Export with forms data
 */
export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'give_forms', { per_page: 30 } ),
	};
} )( Inspector );
