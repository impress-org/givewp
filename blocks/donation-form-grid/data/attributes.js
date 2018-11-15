/**
 * Block Attributes
*/

const blockAttributes = {
	columns: {
		type: 'string',
		default: 'best-fit',
	},
	showExcerpt: {
		type: 'boolean',
		default: true,
	},
	showGoal: {
		type: 'boolean',
		default: true,
	},
	showFeaturedImage: {
		type: 'boolean',
		default: true,
	},
	displayType: {
		type: 'string',
		default: 'redirect',
	},
};

export default blockAttributes;
