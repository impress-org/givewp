import {useDonationFormMultiStepState} from '@givewp/forms/app/form/MultiStepForm/store';
import useSetNextStep from '@givewp/forms/app/form/MultiStepForm/hooks/useSetNextStep';
import {useFormContext} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import {useMemo} from 'react';

/**
 * @unreleased
 */
export default function NextButton() {
    const {steps, currentStep} = useDonationFormMultiStepState();
    const fieldNames = useMemo(() => steps.find(({id}) => id === currentStep)?.fields ?? [], [steps, currentStep]);
    const {trigger, getValues} = useFormContext();
    const setNextStep = useSetNextStep();
    const isLastStep = currentStep === steps.length - 1;

    return (
        !isLastStep && (
            <div>
                <button
                    type="button"
                    onClick={async () => {
                        const valid = await trigger(fieldNames);

                        if (valid) {
                            setNextStep(currentStep, getValues());
                        }
                    }}
                >
                    {__('Continue')}
                </button>
            </div>
        )
    );
}