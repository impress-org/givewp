/**
 * Block dependencies
 */
import GiveToggleControl from '../../components/toggle-control/index';
import GiveSelectControl from '../../components/select-control/index';
import giveFormOptions from '../data/options';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const {InspectorControls} = wp.blocks;
const {TextControl} = wp.components;
const {PanelBody} = wp.components;
const {Component} = wp.element;

/**
 * Render Inspector Controls
*/

class Inspector extends Component {
	constructor(props){
		super(props);

		this.saveSetting = this.saveSetting.bind(this);
	}

	saveSetting(event) {
		const name = event.target.name;

		this.props.setAttributes(
			'checkbox' === event.target.type ?
				{ [name]: ! this.props.attributes[name] } :
				{ [name]: event.target.value }
		);
	}

	render(){
		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Display' ) }>
					<GiveSelectControl
						label={ __( 'Form Format' ) }
						name='displayStyle'
						value={ this.props.attributes.displayStyle }
						options={ giveFormOptions.displayStyles }
						onChange={ this.saveSetting } />
					{
						'reveal' === this.props.attributes.displayStyle && (
							<TextControl
								label={ __( 'Continue Button Title' ) }
								value={ this.props.attributes.continueButtonTitle }
								onChange={ this.saveSetting }
								onBlur={ updateContinueButtonTitle } />
						)
					}
				</PanelBody>
				<PanelBody title={ __( 'Settings' ) }>
					<GiveToggleControl
						label={ __( 'Title' ) }
						name='showTitle'
						checked={ !! this.props.attributes.showTitle }
						onChange={ this.saveSetting } />
					<GiveToggleControl
						label={ __( 'Goal' ) }
						name='showGoal'
						checked={ !! this.props.attributes.showGoal }
						onChange={ this.saveSetting } />
					<GiveToggleControl
						label={ __( 'Content' ) }
						name='contentDisplay'
						checked={ !! this.props.attributes.contentDisplay }
						onChange={ this.saveSetting } />
					{
						this.props.attributes.contentDisplay && (
							<GiveSelectControl
								label={ __( 'Content Position' ) }
								name='showContent'
								value={ this.props.attributes.showContent }
								options={ giveFormOptions.contentPosition }
								onChange={ this.saveSetting } />
						)
					}
				</PanelBody>
			</InspectorControls>
		);
	}
};

export default Inspector;
