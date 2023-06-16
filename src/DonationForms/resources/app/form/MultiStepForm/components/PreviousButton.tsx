import useSetPreviousStep from '@givewp/forms/app/form/MultiStepForm/hooks/useSetPreviousStep';
import {useDonationFormMultiStepState} from '@givewp/forms/app/form/MultiStepForm/store';
import {ReactNode} from 'react';

/**
 * @since 0.4.0
 */
export default function PreviousButton({children}: {children: ReactNode}) {
    const {currentStep} = useDonationFormMultiStepState();
    const setPreviousStep = useSetPreviousStep();

    return (
        currentStep > 0 && (
            <button
                className="givewp-donation-form__steps-header-previous-button"
                type="button"
                onClick={() => setPreviousStep(currentStep)}
            >
                {children}
            </button>
        )
    );
}