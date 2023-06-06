const SET_CURRENT_STEP = 'set_current_step';

/**
 * @unreleased
 */
export default function reducer(state, action) {
    switch (action.type) {
        case SET_CURRENT_STEP:
            return {
                ...state,
                currentStep: action.step,
            };

        default:
            return state;
    }
}

/**
 * @unreleased
 */
export function setCurrentStep(step: number) {
    return {
        type: SET_CURRENT_STEP,
        step,
    };
}
