/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Block Attributes
*/

const blockAttributes = {
	title: {
		type: 'title',
		default: __( 'Back to School Fundraiser', 'give' ),
	},
	image: {
		type: 'string',
		default: '',
	},
};

export default blockAttributes;
