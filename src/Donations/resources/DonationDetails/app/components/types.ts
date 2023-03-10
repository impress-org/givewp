/**
 *
 * @unreleased
 */
import {ReactNode} from 'react';

export interface FormValues {}

export interface FormTemplate {
    data: {
        id: number;
        formId: number;
        purchaseKey: string;
        createdAt: string;
        updatedAt: string;
        status: string;
        type: string;
        mode: string;
        amount: string;
        feeAmountRecovered: null;
        gatewayId: string;
        gateway: string;
        donorId: number;
        firstName: string;
        lastName: string;
        email: string;
        subscriptionId: number;
        billingAddress: {
            country: string;
            address1: string;
            address2: string;
            city: string;
            state: string;
            zip: string;
        };
        anonymous: false;
        gatewayTransactionId: string;
        company: null;
    };
}

export interface ActionContainer {
    label: string;
    type: string;
    value: string | ReactNode;
    showEditDialog?: () => void;
    formField?: JSX.Element;
}

export interface PaymentInformation {
    register: any;
    setValue: any;
    amount: string;
    feeAmountRecovered: number;
    createdAt: string;
    time: string;
    form: {id: number; title: string};
    status: string;
    type: string;
    gateway: string;
}
