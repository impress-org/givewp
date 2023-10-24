import {useDonationFormMultiStepState} from '@givewp/forms/app/form/MultiStepForm/store';
import useSetNextStep from '@givewp/forms/app/form/MultiStepForm/hooks/useSetNextStep';
import {useFormContext} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import {useMemo, useState} from 'react';
import handleValidationRequest from '@givewp/forms/app/utilities/handleValidationRequest';
import getWindowData from '@givewp/forms/app/utilities/getWindowData';
import useGetGatewayById from '@givewp/forms/app/form/MultiStepForm/hooks/useGetGatewayById';

const {validateUrl} = getWindowData();

/**
 * @since 3.0.0
 */
export default function NextButton({buttonText = __('Continue')}: {buttonText?: string}) {
    const {steps, currentStep} = useDonationFormMultiStepState();
    const getGateway = useGetGatewayById();
    const fieldNames = useMemo(() => steps.find(({id}) => id === currentStep)?.fields ?? [], [steps, currentStep]);
    const {trigger, getValues, setError} = useFormContext();
    const setNextStep = useSetNextStep();
    const isLastStep = currentStep === steps.length - 1;
    const [isValidating, setIsValidating] = useState<boolean>(false);

    return (
        !isLastStep && (
            <button
                className="givewp-donation-form__steps-button-next"
                type="button"
                disabled={isValidating}
                aria-busy={isValidating}
                onClick={async () => {
                    setIsValidating(true);
                    const isClientValid = await trigger(fieldNames);

                    if (!isClientValid) {
                        setIsValidating(false);

                        return;
                    }

                    const values = getValues();

                    const isServerValid = await handleValidationRequest(
                        validateUrl,
                        values,
                        setError,
                        getGateway(values?.gatewayId)
                    );

                    setIsValidating(false);

                    if (isServerValid) {
                        setNextStep(currentStep, values);
                    }
                }}
            >
                {buttonText}
            </button>
        )
    );
}
