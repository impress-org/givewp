const UPDATE_SETTINGS = 'update_settings';
const UPDATE_BLOCKS = 'update_blocks';

/**
 * This reducer is used within the FormStateProvider for state management
 *
 * @unreleased
 */
export default function reducer(state, action) {
    switch (action.type) {
        case UPDATE_SETTINGS:
            return {
                ...state,
                settings: {
                    ...state.settings,
                    ...action.settings,
                },
            };
        case UPDATE_BLOCKS:
            return {
                ...state,
                blocks: action.blocks
            };

        default:
            return state;
    }
}

/**
 * @unreleased
 */
export function setFormSettings(settings) {
    return {
        type: UPDATE_SETTINGS,
        settings,
    };
}

/**
 * @unreleased
 */
export function setFormBlocks(blocks) {
    return {
        type: UPDATE_BLOCKS,
        blocks,
    };
}
