import {PaymentElement, useElements, useStripe} from '@stripe/react-stripe-js';

import {useImperativeHandle} from 'react';

const StripePaymentElement = ({currency, gateway, forwardedRef}) => {
    const stripe = useStripe();
    const elements = useElements();

    useImperativeHandle(
        forwardedRef,
        () => ({
            //TODO: handle return url
            async getPaymentMethod() {
                // We don't want to let default form submission happen here,
                // which would refresh the page.
                event.preventDefault();

                if (!stripe || !elements) {
                    // Stripe.js hasn't yet loaded.
                    // Make sure to disable form submission until Stripe.js has loaded.
                    return null;
                }

                const {error} = await stripe.confirmSetup({
                    //`Elements` instance that was used to create the Payment Element
                    elements,
                    confirmParams: {
                        return_url: gateway.returnUrl,
                    },
                });

                if (error) {
                    // This point will only be reached if there is an immediate error when
                    // confirming the payment. Show error to your customer (for example, payment
                    // details incomplete)
                    setErrorMessage(error.message);
                } else {
                    // Your customer will be redirected to your `return_url`. For some payment
                    // methods like iDEAL, your customer will be redirected to an intermediate
                    // site first to authorize the payment, then redirected to the `return_url`.
                }
            },
        }),
        [stripe, elements]
    );

    return (
        <PaymentElement
            options={{
                mode: 'setup',
                currency,
            }}
        />
    );
};

export default StripePaymentElement;
