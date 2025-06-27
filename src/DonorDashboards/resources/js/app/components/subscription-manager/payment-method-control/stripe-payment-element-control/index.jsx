import {PaymentElement, useElements, useStripe} from '@stripe/react-stripe-js';

import {Elements} from '@stripe/react-stripe-js';
import {loadStripe} from '@stripe/stripe-js';
import {useState, useEffect, useImperativeHandle} from 'react';
import StripePaymentElement from './stripe-payment-element';

const StripePaymentElementControl = ({gateway, currency, forwardedRef}) => {
    const [stripePromise, setStripePromise] = useState(null);

    useEffect(() => {
        setStripePromise(loadStripe(gateway.publishableKey, {stripeAccount: gateway.accountId}));
    }, []);

    const fonts = [
        {
            src: 'url(https://fonts.googleapis.com/css2?family=Montserrat:wght@500)',
            family: 'Montserrat',
        },
    ];

    return (
        <Elements stripe={stripePromise} fonts={fonts} options={{
            clientSecret: gateway.clientSecret,
        }}>
            <StripePaymentElement currency={currency} gateway={gateway} forwardedRef={forwardedRef} />
        </Elements>
    );
};

export default StripePaymentElementControl;
