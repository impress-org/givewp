import {FormProvider, useForm, useFormState} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';

import getWindowData from '../utilities/getWindowData';
import {useGiveDonationFormStore} from '../store';
import type {Gateway, Section} from '@givewp/forms/types';
import postData from '../utilities/postData';
import {withTemplateWrapper} from '../templates';
import {useCallback} from 'react';
import SectionNode from '../fields/SectionNode';
import generateRequestErrors from '../utilities/generateRequestErrors';
import FormRequestError from '../errors/FormRequestError';
import DonationReceipt from './DonationReceipt';
import {ObjectSchema} from 'joi';

const {donateUrl} = getWindowData();
const formTemplates = window.givewp.form.templates;

const FormTemplate = withTemplateWrapper(formTemplates.layouts.form);
const FormSectionTemplate = withTemplateWrapper(formTemplates.layouts.section, 'section');

const handleSubmitRequest = async (values, setError, gateway: Gateway) => {
    let beforeCreatePaymentGatewayResponse = {};

    try {
        if (gateway.beforeCreatePayment) {
            beforeCreatePaymentGatewayResponse = await gateway.beforeCreatePayment(values);
        }

        const {response} = await postData(donateUrl, {
            ...values,
            gatewayData: beforeCreatePaymentGatewayResponse,
        });

        if (response.data?.errors) {
            throw new FormRequestError(response.data.errors.errors);
        }

        if (gateway.afterCreatePayment) {
            await gateway.afterCreatePayment(response);
        }
    } catch (error) {
        if (error instanceof FormRequestError) {
            return generateRequestErrors(values, error.errors, setError);
        }

        return setError('FORM_ERROR', {
            message: error?.message ?? 'Something went wrong, please try again or contact support.',
        });
    }
};

export default function Form({defaultValues, sections, validationSchema}: PropTypes) {
    const {gateways} = useGiveDonationFormStore();

    const getGateway = useCallback((gatewayId) => gateways.find(({id}) => id === gatewayId), []);

    const methods = useForm<FormInputs>({
        defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit, setError, getValues, control} = methods;

    const {errors, isSubmitting, isSubmitSuccessful} = useFormState({control});

    const formError = errors.hasOwnProperty('FORM_ERROR') ? errors.FORM_ERROR.message : null;

    if (isSubmitSuccessful) {
        const {amount, firstName, lastName, email, gatewayId} = getValues();
        const gateway = gateways.find(({id}) => id === gatewayId);

        return (
            <DonationReceipt
                amount={amount}
                email={email}
                firstName={firstName}
                lastName={lastName}
                gateway={gateway}
                status={'Complete'}
                total={amount}
            />
        );
    }

    return (
        <FormProvider {...methods}>
            <FormTemplate
                formProps={{
                    id: 'give-next-gen',
                    onSubmit: handleSubmit((values) =>
                        handleSubmitRequest(values, setError, getGateway(values.gatewayId))
                    ),
                }}
                isSubmitting={isSubmitting}
                formError={formError}
            >
                <>
                    {sections.map((section) => {
                        return (
                            <FormSectionTemplate key={section.name} section={section}>
                                {section.nodes.map((node) => (
                                    <SectionNode key={node.name} node={node} />
                                ))}
                            </FormSectionTemplate>
                        );
                    })}
                </>
            </FormTemplate>
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
