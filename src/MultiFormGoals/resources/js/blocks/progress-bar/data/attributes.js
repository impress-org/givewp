/**
 * Block Attributes
*/

const blockAttributes = {
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
	goal: {
		type: 'string',
		default: '1000',
	},
	color: {
		type: 'string',
		default: '#',
	},
};

export default blockAttributes;
