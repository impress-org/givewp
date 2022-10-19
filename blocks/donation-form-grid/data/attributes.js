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
    imageHeightOptions: {
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
	showFeaturedImage: {
		type: 'boolean',
		default: true,
	},
    showDonateButton: {
		type: 'boolean',
		default: true,
	},
    tagBackgroundColor: {
		type: 'string',
		default: '#69b86b',
	},
    tagTextColor: {
        type: 'string',
        default: '#ffffff',
    },
    donateButtonTextColor: {
		type: 'string',
		default: '#69b86b',
	},
	displayType: {
		type: 'string',
		default: 'redirect',
	},
    excerptLength: {
        type: 'integer',
        default: 16
    },
    filterOptions: {
        type: 'string',
        default: 'tags',
    },
    progressBarColor: {
        type: 'string',
        default: '#69b86b'
    }
};

export default blockAttributes;
