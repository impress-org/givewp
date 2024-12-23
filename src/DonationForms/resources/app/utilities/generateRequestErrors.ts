/**
 * Takes a try-catch request exception and sets errors for React Hook Form to consume
 */
import {UseFormSetError} from 'react-hook-form';
import {__} from '@wordpress/i18n';

const generateRequestErrors = (values: Record<string, any>, errors: object[], setError: UseFormSetError<any>) => {
    Object.entries(errors).forEach(([field, value]) => {
        if (Object.keys(values).includes(field)) {
            const fieldElement: HTMLInputElement = document.querySelector('input[name="' + field + '"]');
            const canFocus = fieldElement && fieldElement.type !== 'hidden';

            setError(field, {message: Array.isArray(value) ? value[0] : value}, {shouldFocus: canFocus});

            if (!canFocus) {
                // In fields that aren't inputs by default or are hidden inputs, we need to use this workaround because the "shouldFocus" option will not work in these cases.
                if (!fieldElement) {
                    const fieldElementContainer = document.querySelector('.givewp-fields-' + field); //E.g: <div class="givewp-fields-giftAid">content...</div>
                    fieldElementContainer?.scrollIntoView({behavior: 'smooth'});
                } else {
                    fieldElement.parentElement.scrollIntoView({behavior: 'smooth'}); // E.g: <input type="hidden" name="state">
                }
            }
        } else if (field === 'gateway_error') {
            setError('FORM_ERROR', {message: Array.isArray(value) ? value[0] : value});
        } else {
            setError('FORM_ERROR', {
                message: __('Something went wrong, please try again or contact support.', 'give'),
            });
        }
    });
};

export default generateRequestErrors;
