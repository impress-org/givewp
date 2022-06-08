import {FC} from 'react';

export interface Currency {
    amount: number;
    currency: string;
}

export interface FormData {
    honorific?: string;
    firstName: string;
    lastName?: string;
    email: string;
    amount: number;
    company?: string;
}

export interface Field {
    type: string;
    name: string;
    label: string;
    readOnly: boolean;
    validationRules: {required: boolean};
    nodes?: Field[];
}

export interface FormServerExports {
    gatewaySettings: {
        [key: string]: GatewaySettings;
    };
    form: {
        nodes: Field[];
    };
    attributes: object;
    donateUrl: string;
    successUrl: string;
}

export interface GatewaySettings {
    label: string;
}

export interface Gateway {
    /**
     * The gateway ID. Must be the same as the back-end
     */
    id: string;

    /**
     * Settings for the gateway as sent from the back-end
     */
    settings?: GatewaySettings;

    /**
     * Initialize function for the gateway. The settings are passed to the gateway
     * from the server. This is called once before the form is rendered.
     */
    initialize?(): void;

    /**
     * The component to render when the gateway is selected
     */
    Fields: FC;

    /**
     * Whether the gateway supports recurring donations
     */
    supportsRecurring: boolean;

    /**
     * Whether the gateway supports the given currency
     */
    supportsCurrency(currency: string): boolean;

    /**
     * A hook before the form is submitted.
     */
    beforeCreatePayment?(values: FormData): Promise<object> | Error;

    /**
     * A hook after the form is submitted.
     */
    afterCreatePayment?(response: object): Promise<void> | Error;
}

export interface Template {}
