const UPDATE_DEFAULT_VALUES = 'update_default_values';
const SET_FORM_REFS = 'set_form_refs';

/**
 * @since 3.0.0
 */
export default function reducer(state, action) {
    switch (action.type) {
        case UPDATE_DEFAULT_VALUES:
            return {
                ...state,
                defaultValues: {
                    ...state.values,
                    ...action.values,
                },
            };
        case SET_FORM_REFS:
            return {
                ...state,
                refs: {
                    ...state.refs,
                    ...action.refs,
                },
            };
        default:
            return state;
    }
}

/**
 * @since 3.0.0
 */
export function setFormDefaultValues(values: object) {
    return {
        type: UPDATE_DEFAULT_VALUES,
        values,
    };
}

/**
 * @unreleased
 */
export function setFormRefs(refs: Record<string, any>) {
    return {
        type: SET_FORM_REFS,
        refs,
    };
}