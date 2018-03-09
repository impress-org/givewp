/**
 * Block dependencies
*/
import Inspector from '../inspector';
import Controls from '../controls';

/**
 * Render Form Preview
 */

const FormPreview = ( props ) => {
	return (
		<div id="donation-form-preview-block">
			{ !! props.isSelected && ( <Inspector { ... { ...props } } /> ) }
			<Controls { ... { ...props } } />
			<div dangerouslySetInnerHTML={ { __html: props.html } }></div>
		</div>
	);
};

export default FormPreview;
