/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n'

/**
 * Block Attributes
 */

const blockAttributes = {
    donorsPerPage: {
        type: 'string',
        default: '12',
    },
    formID: {
        type: 'array',
        default: [],
    },
    ids: {
        type: 'array',
        default: [],
    },
    categories: {
        type: 'array',
        default: [],
    },
    tags: {
        type: 'string',
        default: [],
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
        default: '3',
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
    showForm: {
        type: 'boolean',
        default: true,
    },
    showTotal: {
        type: 'boolean',
        default: true,
    },
    showComments: {
        type: 'boolean',
        default: true,
    },
    showTributes: {
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
        default: '80',
    },
    readMoreText: {
        type: 'string',
        default: __('Read more', 'give'),
    },
    loadMoreText: {
        type: 'string',
        default: __('Load more', 'give'),
    },
    avatarSize: {
        type: 'string',
        default: '75',
    },
    toggleOptions: {
        type: 'string',
        default: 'donorInfo',
    },
    filterOptions: {
        type: 'string',
        default: 'ids',
    },
    color: {
        type: 'string',
        default: '#219653',
    },
    showTimestamp: {
        type: 'boolean',
        default: true,
    },
};

export default blockAttributes;
