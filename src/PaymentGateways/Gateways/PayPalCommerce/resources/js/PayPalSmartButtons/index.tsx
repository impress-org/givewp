import {PayPalButtons, usePayPalScriptReducer} from '@paypal/react-paypal-js';
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
import {__} from '@wordpress/i18n';
import handleValidationRequest from '@givewp/forms/app/utilities/handleValidationRequest';

/**
 * @unreleased
 * @see https://paypal.github.io/react-paypal-js/?path=/docs/example-paypalbuttons--default
 */
export default function PayPalSmartButtons({gateway}: {gateway: PayPalCommerceGateway}) {
    const [state] = usePayPalScriptReducer();
    const submitButton = window.givewp.form.hooks.useFormSubmitButton();
    const {setError, trigger, getValues, setFocus, getFieldState} = window.givewp.form.hooks.useFormContext();
    const {submitCount, isSubmitting, isSubmitSuccessful} = window.givewp.form.hooks.useFormState();
    console.log({submitCount});
    console.log({state})
    const formData = new FormData();
    formData.append('_ajax_nonce', gateway.settings.nonce);
    formData.append('firstName', 'John');
    formData.append('lastName', 'Doe');
    formData.append('email', 'jon@givewp.com');
    formData.append('donationAmount', '10.00');
    formData.append('formId', '1');
    formData.append('formTitle', 'Test Form');

    async function handleCreateOrder(data: CreateOrderData, actions: CreateOrderActions): Promise<string> {
        return await createOrder(gateway.settings.createOrderUrl, gateway, formData);
    }

    async function handleOnApprove(data: OnApproveData, actions: OnApproveActions): Promise<void> {
        // call server to authorize the order
        const response = await authorizeOrder({ orderID: data.orderID }, gateway.settings.authorizeOrderUrl, gateway, formData);

        if (response) {
            submitButton?.click();
        }
    }

    async function handleOnClick(data: Record<string, unknown>, actions: OnClickActions): Promise<void> {
        console.log('handleOnClick', {data, actions});
        // attempting to reject the action and redirect logic to the native form submission and gateway object.
        return actions.reject().then(() => {
            console.log('handleOnClickReject', {data, actions});
            gateway.paypalOnClickActions = actions;
            return submitButton?.click();
        });
         // Validate the form values in the client side before proceeding.
        const isClientValidationSuccessful = await trigger();
        if (!isClientValidationSuccessful) {
            // Set focus on first invalid field.
            for (const fieldName in getValues()) {
                if (getFieldState(fieldName).invalid) {
                    setFocus(fieldName);
                }
            }
            return actions.reject();
        }

        const isServerValidationSuccessful = await handleValidationRequest(
            gateway.settings.validateUrl,
            getValues(),
            setError,
            gateway
        );

        if (!isServerValidationSuccessful) {
            return actions.reject();
        }

        gateway.paypalOnClickActions = actions;
        return actions.resolve();
    }

    function handleOnInit(data: Record<string, unknown>, actions: OnInitActions): void {
        //actions.disable().then(r => console.log(r));
    }

    function handleOnError(error) {
        console.error(error);

        setError(
            'FORM_ERROR',
            {
                message: __(
                    'There was an error processing your payment. Please try again.',
                    'give'
                ),
            },
            {shouldFocus: true}
        );
    }

    return (
        <PayPalButtons
            createOrder={handleCreateOrder}
            onApprove={handleOnApprove}
            onClick={handleOnClick}
            onError={handleOnError}
            onInit={handleOnInit}
            disabled={true}
            //disabled={isSubmitting || isSubmitSuccessful}
        />
    );
}
