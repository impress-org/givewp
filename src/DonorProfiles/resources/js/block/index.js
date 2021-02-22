/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import GiveLogo from './logo';
import edit from './edit';

/**
 * Register Block
 */

export default registerBlockType( 'give/donor-profile', {
	title: __( 'Donor Profile', 'give' ),
	description: __( 'The Donor Profile block allows donors to modify and review their donor information from the front-end.', 'give' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	keywords: [
		__( 'donor', 'give' ),
		__( 'profile', 'give' ),
	],
	attributes: blockAttributes,
	supports: {
		align: [
			'wide',
		],
	},
	edit: edit,
	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
