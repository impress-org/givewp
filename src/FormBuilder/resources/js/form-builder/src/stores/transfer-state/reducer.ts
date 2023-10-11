const UPDATE_TRANSFER_STATE = 'update_transfer_state';

export default function reducer(state, action) {
    switch (action.type) {
        case UPDATE_TRANSFER_STATE:
            return {
                ...state,
                ...action.transfer,
            };
        default:
            return state;
    }
}

/**
 * @since 3.0.0
 */
export function setTransferState(transfer) {
    return {
        type: UPDATE_TRANSFER_STATE,
        transfer,
    };
}
