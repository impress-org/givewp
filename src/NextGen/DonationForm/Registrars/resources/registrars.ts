import GatewayRegistrar from './GatewayRegistrar';
import TemplateRegistrar from './TemplateRegistrar';
import {FormServerExports} from '@givewp/forms/types';
import {UseFormReturn} from 'react-hook-form';

if (!window.givewp) {
    // @ts-ignore
    window.givewp = {};
}

window.givewp.gateways = new GatewayRegistrar();
window.givewp.template = new TemplateRegistrar();

declare global {
    interface Window {
        givewp: {
            gateways: GatewayRegistrar;
            template: TemplateRegistrar;
            form: {
                useFormContext(): UseFormReturn;
                useWatch(options: {
                    name: string | string[];
                    defaultValue?: any;
                    control?: object;
                    disabled?: boolean;
                    exact?: boolean;
                }): any;
            };
        };
        giveNextGenExports: FormServerExports;
    }
}
