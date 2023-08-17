const UPDATE_DEFAULT_VALUES = 'update_default_values';

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