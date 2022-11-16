import GatewayRegistrar from './GatewayRegistrar';
import FormDesignRegistrar from './FormDesignRegistrar';
import type {FormServerExports} from '@givewp/forms/types';
import type {useFormContext, useWatch} from 'react-hook-form';

import type {
    getElementTemplate,
    getFieldErrorTemplate,
    getFieldLabelTemplate,
    getFieldTemplate,
    getGroupTemplate,
} from '../../Blocks/DonationFormBlock/resources/app/templates';

if (!window.givewp) {
    // @ts-ignore
    window.givewp = {};
}

window.givewp.gateways = new GatewayRegistrar();
window.givewp.form = {
    ...window.givewp.form,
    designs: new FormDesignRegistrar(),
};

declare global {
    interface Window {
        givewp: {
            gateways: GatewayRegistrar;
            templates: {
                getFieldLabel: typeof getFieldLabelTemplate;
                getFieldError: typeof getFieldErrorTemplate;
                getField: typeof getFieldTemplate;
                getElement: typeof getElementTemplate;
                getGroup: typeof getGroupTemplate;
            };
            form: {
                designs: FormDesignRegistrar;
                hooks: {
                    useFormContext: typeof useFormContext;
                    useWatch: typeof useWatch;
                };
            };
        };
        giveNextGenExports: FormServerExports;
    }
}
