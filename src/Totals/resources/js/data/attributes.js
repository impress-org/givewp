/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Block Attributes
*/

const blockAttributes = {
	message: {
		type: 'string',
		default: __( 'So far, we have {total}. We still need {total_remaining} to reach our goal of {total_goal}!', 'give' ),
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
		default: '1000',
	},
	color: {
		type: 'string',
		default: '#',
	},
	showGoal: {
		type: 'boolean',
		default: true,
	},
	linkText: {
		type: 'string',
		default: __( 'Donate Now', 'give' ),
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
