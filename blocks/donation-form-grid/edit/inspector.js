/**
 * Block dependencies
 */
import GiveToggleControl from '../../components/toggle-control/index';
import GiveSelectControl from '../../components/select-control/index';
import giveFormOptions from "../data/options";


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

class Inspector extends Component{
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
				<PanelBody title={ __( 'Settings' ) }>
					<GiveSelectControl
						label={ __( 'Columns' ) }
						name='columns'
						value={ this.props.attributes.columns }
						options={ giveFormOptions.columns }
						onChange={ this.saveSetting } />
					<GiveToggleControl
						name='showExcerpt'
						label={ __( 'Show Excerpt' ) }
						checked={ !! this.props.attributes.showExcerpt }
						onChange={ this.saveSetting } />
					<GiveToggleControl
						name='showGoal'
						label={ __( 'Show Goal' ) }
						checked={ !! this.props.attributes.showGoal }
						onChange={ this.saveSetting } />
					<GiveToggleControl
						name='showFeaturedImage'
						label={ __( 'Show Featured Image' ) }
						checked={ !! this.props.attributes.showFeaturedImage }
						onChange={ this.saveSetting } />
					<GiveSelectControl
						label={ __( 'Display Type' ) }
						name='displayType'
						value={ this.props.attributes.displayType }
						options={ giveFormOptions.displayType }
						onChange={ this.saveSetting } />
				</PanelBody>
			</InspectorControls>
		);
	}
}

export default Inspector;
