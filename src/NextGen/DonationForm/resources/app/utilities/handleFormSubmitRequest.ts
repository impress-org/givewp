import {
    Gateway,
    isFormResponseGatewayError,
    isFormResponseRedirect,
    isFormResponseValidationError,
    isResponseRedirected,
} from '@givewp/forms/types';
import postData from '../utilities/postData';
import generateRequestErrors from '../utilities/generateRequestErrors';
import FormRequestError from '../errors/FormRequestError';

import {__} from '@wordpress/i18n';
import handleRedirect from '@givewp/forms/app/utilities/handleFormRedirect';

export default async function handleSubmitRequest(
    values,
    setError,
    gateway: Gateway,
    donateUrl: string,
    inlineRedirectRoutes: string[]
) {
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
            await handleRedirect(response.url, inlineRedirectRoutes);
        }

        const formResponse = await response.json();

        if (isFormResponseRedirect(formResponse)) {
            await handleRedirect(formResponse.data.redirectUrl, inlineRedirectRoutes);
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