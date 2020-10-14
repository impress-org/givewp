/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Block Attributes
*/

const blockAttributes = {
	donorsPerPage: {
		type: 'string',
		default: '12',
	},
	formID: {
		type: 'string',
		default: '0',
	},
	ids: {
		type: 'string',
		default: '',
	},
	orderBy: {
		type: 'string',
		default: 'post_date',
	},
	order: {
		type: 'string',
		default: 'DESC',
	},
	paged: {
		type: 'string',
		default: '1',
	},
	columns: {
		type: 'string',
		default: 'best-fit',
	},
	showAvatar: {
		type: 'boolean',
		default: true,
	},
	showName: {
		type: 'boolean',
		default: true,
	},
	showCompanyName: {
		type: 'boolean',
		default: false,
	},
	showTotal: {
		type: 'boolean',
		default: true,
	},
	showDate: {
		type: 'boolean',
		default: true,
	},
	showComments: {
		type: 'boolean',
		default: true,
	},
	showAnonymous: {
		type: 'boolean',
		default: true,
	},
	onlyComments: {
		type: 'boolean',
		default: false,
	},
	commentLength: {
		type: 'string',
		default: '140',
	},
	readMoreText: {
		type: 'string',
		default: __( 'Read more' ),
	},
	loadMoreText: {
		type: 'string',
		default: __( 'Load more' ),
	},
	avatarSize: {
		type: 'string',
		default: '60',
	},
};

export default blockAttributes;
