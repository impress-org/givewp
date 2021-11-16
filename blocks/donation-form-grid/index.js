/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import GiveIcon from '@givewp/components/GiveIcon';
import GiveDonationFormGrid from './edit/block';

/**
 * Register Block
 */

export default registerBlockType( 'give/donation-form-grid', {

	title: __( 'Donation Form Grid' ),
	description: __( 'The GiveWP Donation Form Grid block insert an existing donation form into the page. Each form\'s presentation can be customized below.' ),
	category: 'give',
	icon: <GiveIcon color="grey" />,
	keywords: [
		__( 'donation' ),
		__( 'grid' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: GiveDonationFormGrid,

	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
