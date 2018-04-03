/**
 * Block Attributes
*/

const blockAttributes = {
	id: {
		type: 'number',
		default: 0,
	},
	displayStyle: {
		type: 'string',
		default: 'onpage',
	},
	continueButtonTitle: {
		type: 'string',
		default: '',
	},
	showTitle: {
		type: 'boolean',
		default: false,
	},
	showGoal: {
		type: 'boolean',
		default: false,
	},
	contentDisplay: {
		type: 'boolean',
		default: false,
	},
	showContent: {
		type: 'string',
		default: 'none',
	},
};

export default blockAttributes;
