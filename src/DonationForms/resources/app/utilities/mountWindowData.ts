import {useFormContext, useFormState, useWatch} from 'react-hook-form';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';
import useDonationSummary from '@givewp/forms/app/hooks/useDonationSummary';
import {useDonationFormSettings} from '@givewp/forms/app/store/form-settings';

/**
 *
 * This mounts data to the window object, so it can be accessed by form designs and add-ons
 *
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
    };
}
