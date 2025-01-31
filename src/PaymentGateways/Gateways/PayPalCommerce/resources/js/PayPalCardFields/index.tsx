import {PayPalCardFieldsProvider} from '@paypal/react-paypal-js';
import createOrder from './createOrder';
import authorizeOrder from './authorizeOrder';
import React from 'react';
import type {PayPalCommerceGateway} from '../../../types';
import CardFieldsForm from './CardFieldsForm';
import {CardFieldsOnApproveData} from '@paypal/paypal-js';

function onError(error) {
    console.error(error);
}

/**
 * @unreleased
 * @see https://paypal.github.io/react-paypal-js/?path=/docs/paypal-paypalcardfields-form--default
 */
export default function PayPalCardFields({gateway}: {gateway: PayPalCommerceGateway}) {
    const formData = new FormData();
    formData.append('_ajax_nonce', gateway.settings.nonce);
    formData.append('firstName', 'John');
    formData.append('lastName', 'Doe');
    formData.append('email', 'jon@givewp.com');
    formData.append('donationAmount', '10.00');
    formData.append('formId', '1');
    formData.append('formTitle', 'Test Form');

    const handleCreateOrder = () => {
        return createOrder(gateway.settings.createOrderUrl, gateway, formData);
    };

    const handleOnApprove = (cardData: CardFieldsOnApproveData) => {
        return authorizeOrder(cardData, gateway.settings.authorizeOrderUrl, gateway, formData);
    };

    const handleOnInputSubmitRequest = (event) => {
        console.log('handleOnInputSubmitRequest', event);
    }

    return (
        <PayPalCardFieldsProvider inputEvents={{ onInputSubmitRequest: handleOnInputSubmitRequest}} createOrder={handleCreateOrder} onApprove={handleOnApprove} onError={onError}
        >
            <CardFieldsForm gateway={gateway} />
        </PayPalCardFieldsProvider>
    );
}
