import {FormProvider, useForm, useFormState} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';

import getWindowData from '../utilities/getWindowData';
import {useGiveDonationFormStore} from '../store';
import {
    Gateway,
    isFormResponseGatewayError,
    isFormResponseRedirect,
    isFormResponseValidationError,
    isResponseRedirected,
    Section,
} from '@givewp/forms/types';
import postData from '../utilities/postData';
import {withTemplateWrapper} from '../templates';
import {useCallback} from 'react';
import SectionNode from '../fields/SectionNode';
import generateRequestErrors from '../utilities/generateRequestErrors';
import FormRequestError from '../errors/FormRequestError';
import {ObjectSchema} from 'joi';
import isRouteInlineRedirect from '@givewp/forms/app/utilities/isRouteInlineRedirect';
import {__} from '@wordpress/i18n';

const {donateUrl, inlineRedirectRoutes} = getWindowData();
const formTemplates = window.givewp.form.templates;

const FormTemplate = withTemplateWrapper(formTemplates.layouts.form);
const FormSectionTemplate = withTemplateWrapper(formTemplates.layouts.section, 'section');

async function handleRedirect(url: string) {
    const redirectUrl = new URL(url);
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
    if (values?.donationType === 'subscription' && !gateway.supportsSubscriptions) {
        return setError('FORM_ERROR', {
            message: __(
                'This payment gateway does not support recurring payments, please try selecting another payment gateway.',
                'give'
            ),
        });
    }

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

        const {response} = await postData(donateUrl, {
            ...values,
            originUrl,
            isEmbed,
            embedId: getEmbedId(),
            gatewayData: beforeCreatePaymentGatewayResponse,
        });

        if (isResponseRedirected(response)) {
            await handleRedirect(response.url);
        }

        const formResponse = await response.json();

        if (isFormResponseRedirect(formResponse)) {
            await handleRedirect(formResponse.data.redirectUrl);
        }

        if (isFormResponseGatewayError(formResponse) || isFormResponseValidationError(formResponse)) {
            throw new FormRequestError(formResponse.data.errors.errors);
        }

        if (gateway.afterCreatePayment) {
            await gateway.afterCreatePayment(formResponse);
        }
    } catch (error) {
        if (error instanceof FormRequestError) {
            return generateRequestErrors(values, error.errors, setError);
        }

        return setError('FORM_ERROR', {
            message: error?.message ?? __('Something went wrong, please try again or contact support.', 'give'),
        });
    }
};

export default function Form({defaultValues, sections, validationSchema}: PropTypes) {
    const {gateways} = useGiveDonationFormStore();

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
            <FormTemplate
                formProps={{
                    id: 'give-next-gen',
                    onSubmit: handleSubmit((values) =>
                        handleSubmitRequest(values, setError, getGateway(values.gatewayId))
                    ),
                }}
                isSubmitting={isSubmitting || isSubmitSuccessful}
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
