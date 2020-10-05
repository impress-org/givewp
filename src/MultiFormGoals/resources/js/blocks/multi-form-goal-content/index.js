/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.blockEditor;

/**
 * Internal dependencies
 */
import GiveLogo from '../../components/logo';

/**
 * Required styles (both common and editor styles)
 */
import '../../../css/common.scss';
import '../../../css/editor.scss';

/**
 * Register Block
 */
const blockTemplate = [
	[ 'core/heading', {
		placeholder: __( 'Heading', 'give' ),
	} ],
	[ 'core/paragraph', {
		placeholder: __( 'Summary', 'give' ),
	} ],
];

const allowedBlocks = [
	'core/paragraph',
	'core/buttons',
];

export default registerBlockType( 'give/multi-form-goal-content', {
	title: __( 'Multi-Form Goal Content', 'give' ),
	description: __( 'The Multi-Form Goals block displays progress made across donation forms towards a common goal.', 'give' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	parent: [ 'give/multi-form-goal' ],
	keywords: [
		__( 'donation', 'give' ),
		__( 'multi form goals', 'give' ),
	],
	edit: () => {
		return (
			<div className="give-multi-form-goal-content-block">
				<InnerBlocks
					template={ blockTemplate }
					allowedBlocks={ allowedBlocks }
					templateLock={ false }
				/>
			</div>
		);
	},
	save: () => {
		return (
			<div className="give-multi-form-goal-content-block">
				<InnerBlocks.Content />
			</div>
		);
	},
} );
