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
import getCurrentFormUrlData from '@givewp/forms/app/utilities/getCurrentFormUrlData';

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

        const {originUrl, isEmbed, embedId} = getCurrentFormUrlData();

        const {response} = await postData(donateUrl, {
            ...values,
            originUrl,
            isEmbed,
            embedId,
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