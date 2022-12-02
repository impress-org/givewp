import GatewayRegistrar from './gateways';
import type {FormServerExports} from '@givewp/forms/types';
import type {useFormContext, useFormState, useWatch} from 'react-hook-form';
import defaultFormTemplates from './templates';

declare global {
    interface Window {
        giveNextGenExports: FormServerExports;
        givewp: {
            gateways: GatewayRegistrar;
            form: {
                templates: typeof defaultFormTemplates;
                hooks: {
                    useFormContext: typeof useFormContext;
                    useWatch: typeof useWatch;
                    useFormState: typeof useFormState;
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
