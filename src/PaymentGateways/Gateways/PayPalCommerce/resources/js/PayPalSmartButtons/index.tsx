import {PayPalButtons} from '@paypal/react-paypal-js';
import React from 'react';
import type {PayPalCommerceGateway} from '../../../types';
import {
    CreateOrderActions,
    CreateOrderData,
    OnApproveActions,
    OnApproveData,
    OnClickActions,
    OnInitActions
} from '@paypal/paypal-js';
import createOrder from '../PayPalCardFields/createOrder';
import authorizeOrder from '../PayPalCardFields/authorizeOrder';

/**
 * @unreleased
 * @see https://paypal.github.io/react-paypal-js/?path=/docs/example-paypalbuttons--default
 */
export default function PayPalSmartButtons({gateway}: {gateway: PayPalCommerceGateway}) {
    const formData = new FormData();
    formData.append('_ajax_nonce', gateway.settings.nonce);
    formData.append('firstName', 'John');
    formData.append('lastName', 'Doe');
    formData.append('email', 'jon@givewp.com');
    formData.append('donationAmount', '10.00');
    formData.append('formId', '1');
    formData.append('formTitle', 'Test Form');

    const donationFormWithSubmitButton = Array.from(document.forms).pop();
    console.log({donationFormWithSubmitButton});
    const submitButton: HTMLButtonElement = donationFormWithSubmitButton.querySelector('[type="submit"]');

    async function handleCreateOrder(data: CreateOrderData, actions: CreateOrderActions): Promise<string> {
        return await createOrder(gateway.settings.createOrderUrl, gateway, formData);
    }

    async function handleOnApprove(data: OnApproveData, actions: OnApproveActions): Promise<void> {
        // call server to authorize the order
        const response = await authorizeOrder({ orderID: data.orderID }, gateway.settings.authorizeOrderUrl, gateway, formData);

        if (response) {
            const donationFormWithSubmitButton = Array.from(document.forms).pop();
            console.log({donationFormWithSubmitButton});
            const submitButton: HTMLButtonElement = donationFormWithSubmitButton.querySelector('[type="submit"]');
            submitButton.click();
        }
    }

    function handleOnClick(data: Record<string, unknown>, actions: OnClickActions): void | Promise<void> {
        console.log('handleOnClick', {data, actions});
        gateway.paypalOnClickActions = actions;
        return actions.reject();
    }

    function handleOnInit(data: Record<string, unknown>, actions: OnInitActions): void {
        //actions.disable().then(r => console.log(r));
    }

    function handleOnError(error) {
        console.error(error);
        //gateway.isUsingSmartButtons = false;
    }

    return (
        <PayPalButtons
            createOrder={handleCreateOrder}
            onApprove={handleOnApprove}
            onClick={handleOnClick}
            onError={handleOnError}
            onInit={handleOnInit}
        />
    );
}
