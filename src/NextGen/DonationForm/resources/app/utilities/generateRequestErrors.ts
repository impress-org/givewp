/**
 * Takes a try-catch request exception and sets errors for React Hook Form to consume
 */
import {UseFormSetError} from 'react-hook-form';
import {__} from '@wordpress/i18n';

const generateRequestErrors = (values: Array<any>, errors: Array<Object>, setError: UseFormSetError<any>) => {
    Object.entries(errors).forEach(([field, value]) => {
        if (Object.keys(values).includes(field)) {
            setError(field, {message: Array.isArray(value) ? value[0] : value});
        } else {
            setError('FORM_ERROR', {
                message: __('Something went wrong, please try again or contact support.', 'give'),
            });
        }
    });
};

export default generateRequestErrors;
