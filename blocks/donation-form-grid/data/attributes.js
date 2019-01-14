/**
 * Block Attributes
*/

const blockAttributes = {
	formsPerPage:{
		type: 'string',
		default: '12',
	},
	formIDs: {
		type: 'string',
		default: '',
	},
	excludedFormIDs:{
		type: 'string',
		default: '',
	},
	orderBy:{
		type: 'string',
		default: 'date',
	},
	order:{
		type: 'string',
		default: 'DESC',
	},
	categories:{
		type: 'string',
		default: '',
	},
	tags:{
		type: 'string',
		default: '',
	},
	columns: {
		type: 'string',
		default: 'best-fit',
	},
	showTitle: {
		type: 'boolean',
		default: true,
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
