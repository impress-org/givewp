const UPDATE_DEFAULT_VALUES = 'update_default_values';
const SET_DONOR_AUTHENTICATED = 'set_donor_authenticated';

/**
 * @since TBD Add the set_donor_authenticated action.
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

        case SET_DONOR_AUTHENTICATED:
            return {
                ...state,
                isDonorAuthenticated: true,
                donorEmail: action.email,
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
 * Records that the inline login signed the donor in, and the email the server will require.
 *
 * @since TBD
 *
 * @param email The donor's primary email returned by the login request.
 */
export function setDonorAuthenticated(email: string) {
    return {
        type: SET_DONOR_AUTHENTICATED,
        email,
    };
}
