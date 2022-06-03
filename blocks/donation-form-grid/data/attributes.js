/**
 * Block Attributes
*/

const blockAttributes = {
	formsPerPage:{
		type: 'string',
		default: '12',
	},
    paged:{
        type: 'boolean',
        default: true,
	},
	formIDs: {
		type: 'array',
		default: [],
	},
	excludedFormIDs:{
		type: 'array',
		default: [],
	},
    excludeForms:{
		type: 'boolean',
		default: false,
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
		type: 'array',
		default: [],
	},
	tags:{
		type: 'array',
		default: [],
	},
	columns: {
		type: 'string',
		default: '1',
	},
    imageSize: {
        type: 'string',
        default: 'medium',
    },
    imageHeight: {
        type: 'string',
        default: 'auto',
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
    showProgressBar: {
        type: 'boolean',
        default: false,
    },
	showFeaturedImage: {
		type: 'boolean',
		default: true,
	},
    showDonateButton: {
		type: 'boolean',
		default: false,
	},
    tagBackgroundColor: {
		type: 'string',
		default: '#000000',
	},
    tagTextColor: {
        type: 'string',
        default: '#000000',
    },
    donateButtonTextColor: {
		type: 'string',
		default: '#fff',
	},
	displayType: {
		type: 'string',
		default: 'redirect',
	},
    excerptLength: {
        type: 'integer',
        default: 16
    },
    filter: {
        type: 'string',
        default: 'tags',
    }
};

export default blockAttributes;
