import {StepObject} from "@givewp/forms/app/form/MultiStepForm/types";

/**
 * @unreleased
 */
export default function getCurrentStepObject(steps: StepObject[], currentStep: number) {
    return steps?.find(({id}) => id === currentStep);
}