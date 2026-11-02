import {useFormContext, useFormState, useWatch} from 'react-hook-form';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';
import useDonationSummary from '@givewp/forms/app/hooks/useDonationSummary';
import {useDonationFormSettings} from '@givewp/forms/app/store/form-settings';
import useFormSubmitButton from '@givewp/forms/app/hooks/useFormSubmitButton';
import useFormData from '@givewp/forms/app/hooks/useFormData';
import {useDonationFormState, useDonationFormStateDispatch} from '@givewp/forms/app/store';

/**
 *
 * This mounts data to the window object, so it can be accessed by form designs and add-ons
 *
 * @since 4.19.1.1 Add useDonationFormState and useDonationFormStateDispatch.
 * @since 3.0.0
 */
export default function mountWindowData(): void {
    window.givewp.form.hooks = {
        useFormContext,
        useWatch,
        useFormState,
        useCurrencyFormatter,
        useDonationSummary,
        useDonationFormSettings,
        useFormSubmitButton,
        useFormData,
        useDonationFormState,
        useDonationFormStateDispatch,
    };
}
