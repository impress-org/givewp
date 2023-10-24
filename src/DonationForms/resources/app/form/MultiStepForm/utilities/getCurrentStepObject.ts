import {StepObject} from "@givewp/forms/app/form/MultiStepForm/types";

/**
 * @since 3.0.0
 */
export default function getCurrentStepObject(steps: StepObject[], currentStep: number) {
    return steps?.find(({id}) => id === currentStep);
}