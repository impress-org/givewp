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
	description: __( 'The GiveWP Donor Wall block displays donations on the front end of the site. The settings below allow you to customize the information displayed', 'give' ),
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
