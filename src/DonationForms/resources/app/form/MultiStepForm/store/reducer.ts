import {MultiStepFormContextState} from '@givewp/forms/app/form/MultiStepForm/store/index';
import {visibliityConditionsPass} from '@givewp/forms/app/hooks/useVisibilityCondition';

const SET_CURRENT_STEP = 'set_current_step';
const SET_NEXT_STEP = 'set_next_step';

type Action<T> = {type: typeof SET_CURRENT_STEP; step: T} | {type: typeof SET_NEXT_STEP; formValues: T};

/**
 * @since 3.0.0
 */
export default function reducer<T>(state: MultiStepFormContextState, action: Action<T>) {
    switch (action.type) {
        case SET_CURRENT_STEP:
            return {
                ...state,
                currentStep: action.step,
            };

        case SET_NEXT_STEP:
            const visibleSteps = state.steps.map((step) => {
                const isVisible = step.visibilityConditions.length
                    ? visibliityConditionsPass(step.visibilityConditions, new Map(Object.entries(action.formValues)))
                    : true;

                return {
                    ...step,
                    isVisible,
                };
            });

            return {
                ...state,
                steps: visibleSteps,
                currentStep: visibleSteps.findIndex((step, index) => step.isVisible && index > state.currentStep),
            };

        default:
            return state;
    }
}

/**
 * @since 3.0.0
 */
export function setCurrentStep(step: number) {
    return {
        type: SET_CURRENT_STEP,
        step,
    };
}

export function setNextStep(formValues) {
    return {
        type: SET_NEXT_STEP,
        formValues,
    };
}
