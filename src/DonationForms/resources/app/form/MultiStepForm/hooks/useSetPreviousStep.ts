import {useDonationFormMultiStepStateDispatch} from '@givewp/forms/app/form/MultiStepForm/store';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import {useCallback} from 'react';

/**
 * @since 3.0.0
 */
export default function useSetPreviousStep() {
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();

    return useCallback((currentStep: number) => {
        const previousStep = currentStep - 1;

        if (previousStep <= 0) {
            dispatchMultiStep(setCurrentStep(0));
        } else {
            dispatchMultiStep(setCurrentStep(previousStep));
        }
    }, [dispatchMultiStep]);
}