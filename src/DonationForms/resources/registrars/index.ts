import GatewayRegistrar from './gateways';
import type {DonationConfirmationReceiptServerExports, FormServerExports} from '@givewp/forms/types';
import type {useFormContext, useFormState, useWatch} from 'react-hook-form';
import defaultFormTemplates from './templates';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';
import useDonationSummary from '@givewp/forms/app/hooks/useDonationSummary';
import {useDonationFormSettings} from '@givewp/forms/app/store/form-settings';

declare global {
    interface Window {
        givewpDonationFormExports: FormServerExports;
        givewpDonationConfirmationReceiptExports: DonationConfirmationReceiptServerExports;
        givewp: {
            gateways: GatewayRegistrar;
            form: {
                templates: typeof defaultFormTemplates;
                hooks: {
                    useFormContext: typeof useFormContext;
                    useWatch: typeof useWatch;
                    useFormState: typeof useFormState;
                    useCurrencyFormatter: typeof useCurrencyFormatter;
                    useDonationSummary: typeof useDonationSummary;
                    useDonationFormSettings: typeof useDonationFormSettings;
                };
            };
        };
    }
}

if (!window.givewp) {
    // @ts-ignore
    window.givewp = {
        // @ts-ignore
        form: {},
    };
}

window.givewp.gateways = new GatewayRegistrar();
window.givewp.form.templates = Object.freeze(defaultFormTemplates);
