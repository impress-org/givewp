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
	metric: {
		type: 'string',
		default: 'revenue',
	},
	goal: {
		type: 'string',
		default: '1000',
	},
	deadline: {
		type: 'string',
		default: '',
	},
	color: {
		type: 'string',
		default: '#',
	},
};

export default blockAttributes;
