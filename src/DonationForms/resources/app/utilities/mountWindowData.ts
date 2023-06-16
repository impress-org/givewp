import {useFormContext, useFormState, useWatch} from 'react-hook-form';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';
import useDonationSummary from '@givewp/forms/app/hooks/useDonationSummary';

/**
 *
 * This mounts data to the window object, so it can be accessed by form designs and add-ons
 *
 * @since 0.4.0 add useDonationSummary
 * @since 0.1.0
 */
export default function mountWindowData(): void {
    window.givewp.form.hooks = {
        useFormContext,
        useWatch,
        useFormState,
        useCurrencyFormatter,
        useDonationSummary,
    };
}
