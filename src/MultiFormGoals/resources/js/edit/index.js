/**
 * WordPress dependencies
 */
const { Fragment } = wp.element;
const { InnerBlocks } = wp.blockEditor;
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const MultiFormGoals = ( { attributes, setAttributes } ) => {
	const blockTemplate = [
		[ 'core/media-text', {}, [
			[ 'core/heading', { placeholder: __( 'Heading', 'give' ) } ],
			[ 'core/paragraph', { placeholder: __( 'Summary', 'give' ) } ],
		] ],
		[ 'give/progress-bar', {} ],
	];

	return (
		<Fragment>
			<Inspector { ... { attributes, setAttributes } } />
			<div className="give-multi-form-goals">
				<InnerBlocks
					template={ blockTemplate }
					templateLock="all"
				/>
			</div>
		</Fragment>
	);
};

export default MultiFormGoals;
