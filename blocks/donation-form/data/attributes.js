/**
 * Block Attributes
 */
const blockAttributes = {
    id: {
        type: 'number',
        default: 0,
    },
    prevId: {
        type: 'number',
    },
    displayStyle: {
        type: 'string',
        default: 'onpage',
    },
    continueButtonTitle: {
        type: 'string',
        default: 'Donate now',
    },
    showTitle: {
        type: 'boolean',
        default: true,
    },
    showGoal: {
        type: 'boolean',
        default: true,
    },
    contentDisplay: {
        type: 'boolean',
        default: true,
    },
    showContent: {
        type: 'string',
        default: 'above',
    },
    blockId: {
        type: 'string',
    },
};

export default blockAttributes;
