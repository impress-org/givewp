import {Gateway, isFormResponseGatewayError, isFormResponseValidationError} from '@givewp/forms/types';
import generateRequestErrors from '../utilities/generateRequestErrors';
import FormRequestError from '../errors/FormRequestError';

import {__} from '@wordpress/i18n';
import {FieldValues, UseFormSetError} from 'react-hook-form';
import postFormData from '@givewp/forms/app/utilities/postFormData';
import convertValuesToFormData from '@givewp/forms/app/utilities/convertValuesToFormData';

/**
 * @since 3.0.0
 */
export default async function handleValidationRequest(
    validateUrl: string,
    values: FieldValues,
    setError: UseFormSetError<FieldValues>,
    gateway?: Gateway
) {
    if (gateway !== undefined && values?.donationType === 'subscription' && !gateway.supportsSubscriptions) {
        return setError('FORM_ERROR', {
            message: __(
                'This payment gateway does not support recurring payments, please try selecting another payment gateway.',
                'give'
            ),
        });
    }

    try {
        const formData = convertValuesToFormData(values);

        const {response} = await postFormData(validateUrl, formData);

        const formResponse = await response.json();

        if (isFormResponseGatewayError(formResponse) || isFormResponseValidationError(formResponse)) {
            throw new FormRequestError(formResponse.data.errors.errors);
        } else {
            return true;
        }
    } catch (error) {
        if (error instanceof FormRequestError) {
            return generateRequestErrors(values, error.errors, setError);
        }

        return setError('FORM_ERROR', {
            message: error?.message ?? __('Something went wrong, please try again or contact support.', 'give'),
        });
    }
}
