const UPDATE_SETTINGS = 'update_settings';
const UPDATE_BLOCKS = 'update_blocks';
const UPDATE_DIRTY = 'update_dirty';

/**
 * This reducer is used within the FormStateProvider for state management
 *
 * @since 3.0.0
 */
export default function reducer(state, action) {
    switch (action.type) {
        case UPDATE_SETTINGS:
            return {
                ...state,
                isDirty: true,
                settings: {
                    ...state.settings,
                    ...action.settings,
                },
            };
        case UPDATE_BLOCKS:
            return {
                ...state,
                isDirty: true,
                blocks: action.blocks,
            };

        case UPDATE_DIRTY:
            return {
                ...state,
                isDirty: action.isDirty,
            };

        default:
            return state;
    }
}

/**
 * @since 3.0.0
 */
export function setFormSettings(settings) {
    return {
        type: UPDATE_SETTINGS,
        settings,
    };
}

/**
 * @since 3.0.0
 */
export function setFormBlocks(blocks) {
    return {
        type: UPDATE_BLOCKS,
        blocks,
    };
}

/**
 * @since 3.0.0
 */
export function setIsDirty(isDirty: boolean) {
    return {
        type: UPDATE_DIRTY,
        isDirty,
    };
}
