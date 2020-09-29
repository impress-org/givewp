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
	[ 'core/media-text', {}, [
		[ 'core/heading', { placeholder: __( 'Heading', 'give' ) } ],
		[ 'core/paragraph', { placeholder: __( 'Summary', 'give' ) } ],
	] ],
	[ 'give/progress-bar', {} ],
];

export default registerBlockType( 'give/multi-form-goal', {
	title: __( 'Multi-Form Goal', 'give' ),
	description: __( 'The Multi-Form Goals block displays progress made across donation forms towards a common goal.', 'give' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
		__( 'multi form goals', 'give' ),
	],
	edit: () => {
		return (
			<div className="give-multi-form-goal-block">
				<InnerBlocks
					template={ blockTemplate }
					templateLock="all"
				/>
			</div>
		);
	},
	save: () => {
		return (
			<div className="give-multi-form-goal-block">
				<InnerBlocks.Content />
			</div>
		);
	},
} );
