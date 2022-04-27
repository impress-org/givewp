/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import './style.scss';
import GiveIcon from '@givewp/components/GiveIcon';
import blockAttributes from './data/attributes';
import GiveForm from './edit/block';

/**
 * Register Block
*/

export default registerBlockType( 'give/donation-form', {

	title: __( 'Donation Form', 'give' ),
	description: __( 'The GiveWP Donation Form block inserts an existing donation form into the page. Each donation form\'s presentation can be customized below.', 'give' ),
	category: 'give',
	icon: <GiveIcon color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: GiveForm,

	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
