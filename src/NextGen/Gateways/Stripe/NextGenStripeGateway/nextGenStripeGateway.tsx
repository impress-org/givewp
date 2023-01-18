import {loadStripe, Stripe, StripeElements} from '@stripe/stripe-js';
import {Elements, PaymentElement, useElements, useStripe} from '@stripe/react-stripe-js';
import type {Gateway, GatewaySettings} from '@givewp/forms/types';

const StripeFields = ({gateway}) => {
    const stripe = useStripe();
    const elements = useElements();

    gateway.stripe = stripe;
    gateway.elements = elements;

    return <PaymentElement />;
};

let stripePromise = null;
let stripeElementOptions = null;

interface StripeSettings extends GatewaySettings {
    stripeKey: string;
    stripeConnectAccountId: string;
    stripeClientSecret: string;
    successUrl: string;
    stripePaymentIntentId: string;
}

interface StripeGateway extends Gateway {
    stripe?: Stripe;
    elements?: StripeElements;
    settings?: StripeSettings;
}

const stripeGateway: StripeGateway = {
    id: 'next-gen-stripe',
    supportsRecurring: true,
    supportsCurrency(currency: string): boolean {
        return true;
    },
    initialize() {
        const {stripeKey, stripeConnectAccountId, stripeClientSecret} = this.settings;

        /**
         * Create the Stripe object and pass our api keys
         */
        stripePromise = loadStripe(stripeKey, {
            stripeAccount: stripeConnectAccountId,
        });

        stripeElementOptions = {
            clientSecret: stripeClientSecret,
        };
    },
    beforeCreatePayment: async function (values): Promise<object> {
        if (!this.stripe || !this.elements) {
            // Stripe.js has not yet loaded.
            // Make sure to disable form submission until Stripe.js has loaded.
            return;
        }

        return {
            ...this.settings,
        };
    },
    afterCreatePayment: async function (response: {
        data: {
            intentStatus: string;
            returnUrl: string;
        }
    }): Promise<void> {
        if (response.data.intentStatus === 'requires_payment_method') {
            const {error: fetchUpdatesError} = await this.elements.fetchUpdates();

            if (fetchUpdatesError) {
                throw new Error(fetchUpdatesError.message);
            }
        }

        const {error} = await this.stripe.confirmPayment({
            elements: this.elements,
            confirmParams: {
                return_url: response.data.returnUrl,
            },
        });

        // This point will only be reached if there is an immediate error when
        // confirming the payment. Otherwise, your customer will be redirected to
        // your `return_url`. For some payment methods like iDEAL, your customer will
        // be redirected to an intermediate site first to authorize the payment, then
        // redirected to the `return_url`.
        if (error.type === 'card_error' || error.type === 'validation_error') {
            throw new Error(error.message);
        } else if (error) {
            throw new Error(error.message);
        }
    },
    Fields() {
        return (
            <Elements stripe={stripePromise} options={stripeElementOptions}>
                <StripeFields gateway={stripeGateway} />
            </Elements>
        );
    },
};

window.givewp.gateways.register(stripeGateway);
