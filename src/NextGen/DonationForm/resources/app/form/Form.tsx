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
import {ObjectSchema} from 'joi';
import isRouteInlineRedirect from '@givewp/forms/app/utilities/isRouteInlineRedirect';

const {donateUrl, inlineRedirectRoutes} = getWindowData();
const formTemplates = window.givewp.form.templates;

const FormTemplate = withTemplateWrapper(formTemplates.layouts.form);
const FormSectionTemplate = withTemplateWrapper(formTemplates.layouts.section, 'section');

async function handleRedirect(response: any | Response) {
    const redirectUrl = new URL(response.url);
    const redirectUrlParams = new URLSearchParams(redirectUrl.search);
    const shouldRedirectInline = isRouteInlineRedirect(redirectUrlParams, inlineRedirectRoutes);

    if (shouldRedirectInline) {
        // redirect inside iframe
        window.location.assign(redirectUrl);
    } else {
        // redirect outside iframe
        window.top.location.assign(redirectUrl);
    }
}

const handleSubmitRequest = async (values, setError, gateway: Gateway) => {
    let beforeCreatePaymentGatewayResponse = {};

    try {
        if (gateway.beforeCreatePayment) {
            beforeCreatePaymentGatewayResponse = await gateway.beforeCreatePayment(values);
        }

        const originUrl = window.top.location.href;

        const isEmbed = window.frameElement !== null;

        const getEmbedId = () => {
            if (!isEmbed) {
                return null;
            }

            if (window.frameElement.hasAttribute('data-givewp-embed-id')) {
                return window.frameElement.getAttribute('data-givewp-embed-id');
            }

            return window.frameElement.id;
        };

        const {response, isRedirect} = await postData(donateUrl, {
            ...values,
            originUrl,
            isEmbed,
            embedId: getEmbedId(),
            gatewayData: beforeCreatePaymentGatewayResponse,
        });

        if (isRedirect) {
            await handleRedirect(response);
        }

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

    const {handleSubmit, setError, control} = methods;

    const {errors, isSubmitting} = useFormState({control});

    const formError = errors.hasOwnProperty('FORM_ERROR') ? errors.FORM_ERROR.message : null;

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
