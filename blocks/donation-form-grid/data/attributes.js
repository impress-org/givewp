/**
 * Block Attributes
*/

const blockAttributes = {
	columns: {
		type: 'string',
		default:'4',
	},
	showExcerpt: {
		type: 'boolean',
		default: false,
	},
	showGoal: {
		type: 'boolean',
		default: false,
	},
	showFeaturedImage: {
		type: 'boolean',
		default: false,
	},
	displayType: {
		type: 'string',
		default: 'redirect',
	}
};

export default blockAttributes;
