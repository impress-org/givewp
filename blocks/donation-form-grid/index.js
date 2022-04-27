/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks';

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

	title: __( 'Donation Form Grid', 'give' ),
	description: __( 'The GiveWP Donation Form Grid block insert an existing donation form into the page. Each form\'s presentation can be customized below.', 'give' ),
	category: 'give',
	icon: <GiveIcon color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
		__( 'grid', 'give' ),
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
