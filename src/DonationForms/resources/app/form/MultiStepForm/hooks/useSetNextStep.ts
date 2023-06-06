import {useDonationFormMultiStepStateDispatch} from '@givewp/forms/app/form/MultiStepForm/store';
import {setCurrentStep} from '@givewp/forms/app/form/MultiStepForm/store/reducer';
import {useDonationFormStateDispatch} from '@givewp/forms/app/store';
import {setFormDefaultValues} from '@givewp/forms/app/store/reducer';
import {FieldValues} from 'react-hook-form';
import {useCallback} from 'react';

/**
 * @unreleased
 */
export default function useSetNextStep() {
    const dispatchForm = useDonationFormStateDispatch();
    const dispatchMultiStep = useDonationFormMultiStepStateDispatch();

    return useCallback((currentStep: number, formValues: FieldValues) => {
        dispatchForm(setFormDefaultValues(formValues));

        dispatchMultiStep(setCurrentStep(currentStep + 1));
    }, [dispatchForm, dispatchMultiStep]);
}