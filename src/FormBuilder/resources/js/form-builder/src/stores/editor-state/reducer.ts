import EditorMode from "@givewp/form-builder/types/editorMode";

const UPDATE_EDITOR_MODE = 'update_editor_mode';

/**
 * This reducer is used within the FormStateProvider for state management
 *
 * @since 3.0.0
 */
export default function reducer(state, action) {
    switch (action.type) {
        case UPDATE_EDITOR_MODE:
            return {
                ...state,
                mode: action.mode,
            };

        default:
            return state;
    }
}

/**
 * @since 3.0.0
 */
export function setEditorMode(mode: EditorMode) {
    return {
        type: UPDATE_EDITOR_MODE,
        mode,
    };
}
