/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Block Attributes
*/

const blockAttributes = {
	title: {
		type: 'string',
		default: __( 'Back to School Fundraiser', 'give' ),
	},
	description: {
		type: 'string',
		default: __( 'This is a sample description.', 'give' ),
	},
};

export default blockAttributes;
