import {
    PayPalCardFieldsProvider,
    PayPalScriptProvider,
} from '@paypal/react-paypal-js';
import createOrder from './createOrder';
import onApprove from './onApprove';
import React, {useState} from 'react';
import {PayPalCommerceGateway} from '../../../types';
import FormFields from './FormFields';
import {CardFieldsOnApproveData} from '@paypal/paypal-js';

 function onError(error) {
    console.error(error);
}

/**
 * @unreleased
 * @see https://paypal.github.io/react-paypal-js/?path=/docs/paypal-paypalcardfields-form--default
 */
export default function PayPalCardFields({clientId, gateway}: {clientId: string, gateway: PayPalCommerceGateway}) {
    const [isPaying, setIsPaying] = useState<boolean>(false);
    const formData = new FormData();
    formData.append('_ajax_nonce', gateway.settings.nonce);
    formData.append('firstName', 'John');
    formData.append('lastName', 'Doe');
    formData.append('email', 'jon@givewp.com');
    formData.append('donationAmount', '10.00');
    formData.append('formId', '1');
    formData.append('formTitle', 'Test Form');

    const handleCreateOrder = () => {
        return createOrder(gateway.settings.createOrderUrl, formData);
    }

    const handleOnApprove = (data: CardFieldsOnApproveData) => {
        return onApprove(data, gateway)
    }

    return (
        <PayPalScriptProvider
            options={{
                clientId,
                components: 'card-fields',
            }}
        >
            <PayPalCardFieldsProvider
                createOrder={handleCreateOrder}
                onApprove={handleOnApprove}
                onError={onError}
            >
                <FormFields gateway={gateway} />
            </PayPalCardFieldsProvider>
        </PayPalScriptProvider>
    );
}
