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
	enddate: {
		type: 'string',
		default: '',
	},
	color: {
		type: 'string',
		default: '#66bb6a',
	},
};

export default blockAttributes;
