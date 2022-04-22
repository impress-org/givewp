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
import GiveDonorWallGrid from './edit/block';

/**
 * Register Block
 */

export default registerBlockType( 'give/donor-wall', {
	title: __( 'Donor Wall', 'give' ),
	description: __( 'The GiveWP Donor Wall block inserts an existing donation form into the page. Each form\'s presentation can be customized below.', 'give' ),
	category: 'give',
	icon: <GiveIcon color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
		__( 'wall', 'give' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: GiveDonorWallGrid,

	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
