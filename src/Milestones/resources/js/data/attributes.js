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
};

export default blockAttributes;
