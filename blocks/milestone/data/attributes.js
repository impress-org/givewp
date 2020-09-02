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
	forms: {
		type: 'array',
		default: [],
	},
};

export default blockAttributes;
