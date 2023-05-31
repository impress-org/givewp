import {FormProvider, useForm, useFormState} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';
import {ReactNode} from 'react';
import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';
import handleSubmitRequest from '@givewp/forms/app/utilities/handleFormSubmitRequest';
import {useDonationFormState} from '@givewp/forms/app/store';
import {FormInputs} from '@givewp/forms/app/form/MultiStepForm/types';
import SubmitButton from '@givewp/forms/app/form/MultiStepForm/components/SubmitButton';
import {withTemplateWrapper} from '@givewp/forms/app/templates';
import getWindowData from '@givewp/forms/app/utilities/getWindowData';
import NextButton from '@givewp/forms/app/form/MultiStepForm/components/NextButton';
import useGetGatewayById from '@givewp/forms/app/form/MultiStepForm/hooks/useGetGatewayById';

const {donateUrl, inlineRedirectRoutes} = getWindowData();
const formTemplates = window.givewp.form.templates;

const MultiStepFormTemplate = withTemplateWrapper(formTemplates.layouts.multiStepForm);
/**
 * @unreleased
 */
export default function StepForm({
    currentStep,
    isFirstStep,
    isLastStep,
    children,
}: {
    children: ReactNode;
    currentStep: number;
    isFirstStep: boolean;
    isLastStep: boolean;
}) {
    const {defaultValues, validationSchema} = useDonationFormState();
    const getGateway = useGetGatewayById();

    const methods = useForm<FormInputs>({
        defaultValues,
        resolver: joiResolver(validationSchema),
        reValidateMode: 'onBlur',
    });

    const {handleSubmit, setError, control, getValues, trigger} = methods;

    const {errors, isSubmitting, isSubmitSuccessful} = useFormState({control});

    const formError = errors.hasOwnProperty('FORM_ERROR') ? errors.FORM_ERROR.message : null;

    return (
        <FormProvider {...methods}>
            <DonationFormErrorBoundary>
                <MultiStepFormTemplate
                    formProps={{
                        id: 'givewp-donation-form',
                        onSubmit: handleSubmit((values) =>
                            handleSubmitRequest(
                                values,
                                setError,
                                getGateway(values.gatewayId),
                                donateUrl,
                                inlineRedirectRoutes
                            )
                        ),
                    }}
                    isSubmitting={isSubmitting || isSubmitSuccessful}
                    formError={formError}
                    previousButton={null}
                    nextButton={
                        !isLastStep && (
                            <div>
                                <NextButton />
                            </div>
                        )
                    }
                    submitButton={isLastStep && <SubmitButton isSubmitting={isSubmitting || isSubmitSuccessful} />}
                >
                    {children}
                </MultiStepFormTemplate>
            </DonationFormErrorBoundary>
        </FormProvider>
    );
}
