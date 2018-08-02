/**
 * Block dependencies
*/
import Inspector from '../inspector';
import Controls from '../controls';

/**
 * Internal dependencies
 */
const { Component } = wp.element;

/**
 * Render Form Preview
 */

class FormPreview extends Component {
	render() {
		return (
			<div id="donation-form-preview-block">
				<Inspector { ... { ...this.props } } />
				<Controls { ... { ...this.props } } />
				<div dangerouslySetInnerHTML={ { __html: this.props.html } }></div>
			</div>
		);
	}
}

export default FormPreview;
