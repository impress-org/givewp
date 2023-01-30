const UPDATE_SETTINGS = 'update_settings';
const UPDATE_BLOCKS = 'update_blocks';

/**
 * This reducer is used within the FormStateProvider for state management
 *
 * @since 0.1.0
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
 * @since 0.1.0
 */
export function setFormSettings(settings) {
    return {
        type: UPDATE_SETTINGS,
        settings,
    };
}

/**
 * @since 0.1.0
 */
export function setFormBlocks(blocks) {
    return {
        type: UPDATE_BLOCKS,
        blocks,
    };
}
