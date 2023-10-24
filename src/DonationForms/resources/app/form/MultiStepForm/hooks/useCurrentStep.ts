import {useDonationFormMultiStepState} from '@givewp/forms/app/form/MultiStepForm/store';
import getCurrentStepObject from '@givewp/forms/app/form/MultiStepForm/utilities/getCurrentStepObject';
import {StepObject} from '@givewp/forms/app/form/MultiStepForm/types';

/**
 * @since 3.0.0
 */
export default function useCurrentStep(): StepObject {
    const {steps, currentStep} = useDonationFormMultiStepState();

    return getCurrentStepObject(steps, currentStep);
}