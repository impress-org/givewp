/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Block Attributes
*/

const blockAttributes = {
	description: {
		type: 'string',
		default: __( 'But we still need {total_remaining} to reach our goal!', 'give' ),
	},
	ids: {
		type: 'array',
		default: [],
	},
	categories: {
		type: 'array',
		default: [],
	},
	tags: {
		type: 'array',
		default: [],
	},
	metric: {
		type: 'string',
		default: 'revenue',
	},
	goal: {
		type: 'string',
		default: '',
	},
	linkText: {
		type: 'string',
		default: __( 'Learn More', 'give' ),
	},
	linkUrl: {
		type: 'string',
		default: '',
	},
	linkTarget: {
		type: 'string',
		default: '_self',
	},
};

export default blockAttributes;
