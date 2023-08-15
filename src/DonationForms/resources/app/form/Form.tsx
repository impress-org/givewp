import {FormProvider, useForm, useFormState} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';

import getWindowData from '../utilities/getWindowData';
import {useDonationFormState} from '../store';
import {Section} from '@givewp/forms/types';
import {withTemplateWrapper} from '../templates';
import {useCallback} from 'react';
import FormSection from './Section';

import {ObjectSchema} from 'joi';
import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';
import handleSubmitRequest from '@givewp/forms/app/utilities/handleFormSubmitRequest';
import {DonationSummaryProvider} from '@givewp/forms/app/store/donation-summary';

const {donateUrl, inlineRedirectRoutes} = getWindowData();
const formTemplates = window.givewp.form.templates;

const FormTemplate = withTemplateWrapper(formTemplates.layouts.form);

export default function Form({defaultValues, sections, validationSchema}: PropTypes) {
    const {gateways} = useDonationFormState();

    const getGateway = useCallback((gatewayId) => gateways.find(({id}) => id === gatewayId), []);

    const methods = useForm<FormInputs>({
        defaultValues,
        resolver: joiResolver(validationSchema),
        reValidateMode: 'onBlur',
    });

    const {handleSubmit, setError, control} = methods;

    const {errors, isSubmitting, isSubmitSuccessful} = useFormState({control});

    const formError = errors.hasOwnProperty('FORM_ERROR') ? errors.FORM_ERROR.message : null;

    return (
        <FormProvider {...methods}>
            <DonationFormErrorBoundary>
                <DonationSummaryProvider>
                    <FormTemplate
                        formProps={{
                            id: 'give-next-gen',
                            onSubmit: handleSubmit((values: any) =>
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
                    >
                        <>
                            {sections.map((section) => {
                                return (
                                    <DonationFormErrorBoundary key={section.name}>
                                        <FormSection key={section.name} section={section} />
                                    </DonationFormErrorBoundary>
                                );
                            })}
                        </>
                    </FormTemplate>
                </DonationSummaryProvider>
            </DonationFormErrorBoundary>
        </FormProvider>
    );
}

type PropTypes = {
    sections: Section[];
    defaultValues: object;
    validationSchema: ObjectSchema;
};

type FormInputs = {
    FORM_ERROR: string;
    amount: number;
    firstName: string;
    lastName: string;
    email: string;
    gatewayId: string;
};
