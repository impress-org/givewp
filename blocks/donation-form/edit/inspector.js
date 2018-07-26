/**
 * Block dependencies
 */
import GiveTextControl from '../../components/text-control/index';
import GiveToggleControl from '../../components/toggle-control/index';
import GiveSelectControl from '../../components/select-control/index';
import giveFormOptions from '../data/options';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const {InspectorControls} = wp.editor;
const {PanelBody} = wp.components;
const {Component} = wp.element;

/**
 * Render Inspector Controls
*/

class Inspector extends Component {
	constructor(props){
		super(props);

		this.state = {
			continueButtonTitle: this.props.attributes.continueButtonTitle
		};

		this.saveSetting = this.saveSetting.bind(this);
		this.saveState = this.saveState.bind(this);
	}

	saveSetting(event) {
		const name = event.target.name;

		console.log(name);

		this.props.setAttributes(
			'checkbox' === event.target.type ?
				{ [name]: ! this.props.attributes[name] } :
				{ [name]: event.target.value }
		);
	}

	saveState( event ){
		this.setState({ [event.target.name] : event.target.value });
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
							<GiveTextControl
								name='continueButtonTitle'
								label={ __( 'Continue Button Title' ) }
								value={ this.state.continueButtonTitle }
								onChange={ this.saveState }
								onBlur={ this.saveSetting } />
						)
					}
				</PanelBody>
				<PanelBody title={ __( 'Settings' ) }>
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
