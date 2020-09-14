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
		default: __( 'We\'ve raised {total} so far!', 'give' ),
	},
	description: {
		type: 'string',
		default: __( 'But we still need {total_remaining} to reach our goal!', 'give' ),
	},
	image: {
		type: 'string',
		default: '',
	},
	ids: {
		type: 'array',
		default: [],
	},
	deadline: {
		type: 'string',
		default: '',
	},
	goal: {
		type: 'string',
		default: '',
	},
};

export default blockAttributes;
