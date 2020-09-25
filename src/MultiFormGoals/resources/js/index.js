/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import GiveLogo from './components/logo';
import MultiFormGoals from './edit';

/**
 * Required styles (both common and editor styles)
 */
import '../css/common.scss';
import '../css/editor.scss';

/**
 * Register Block
 */

export default registerBlockType( 'give/multi-form-goals', {
	title: __( 'Multi-Form Goals', 'give' ),
	description: __( 'The Multi-Form Goals block displays progress made across donation forms towards a common goal.', 'give' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
		__( 'multi-form-goals', 'give' ),
	],
	attributes: blockAttributes,
	edit: MultiFormGoals,
} );
