import {
    loadStripe,
    Stripe,
    StripeElements,
    StripeElementsOptionsMode,
    StripePaymentElementChangeEvent,
} from '@stripe/stripe-js';
import {Elements, PaymentElement, useElements, useStripe} from '@stripe/react-stripe-js';
import {applyFilters} from '@wordpress/hooks';
import type {Gateway, GatewaySettings} from '@givewp/forms/types';
import {__, sprintf} from '@wordpress/i18n';

let stripePromise = null;
let stripePaymentMethod = null;
let stripePaymentMethodIsCreditCard = false;

// @see https://stripe.com/docs/currencies#zero-decimal
const zeroDecimalCurrencies = [
    'BIF',
    'CLP',
    'DJF',
    'GNF',
    'JPY',
    'KMF',
    'KRW',
    'MGA',
    'PYG',
    'RWF',
    'UGX',
    'VND',
    'VUV',
    'XAF',
    'XOF',
    'XPF',
];

/**
 * Takes in an amount value in dollar units and returns the calculated cents amount
 *
 * @since 3.0.0
 */
const dollarsToCents = (amount: string, currency: string) => {
    if (zeroDecimalCurrencies.includes(currency)) {
        return Math.round(parseFloat(amount));
    }

    return Math.round(parseFloat(amount) * 100);
};

const StripeFields = ({gateway}) => {
    const stripe = useStripe();
    const elements = useElements();

    gateway.stripe = stripe;
    gateway.elements = elements;
    const handleOnChange = (event: StripePaymentElementChangeEvent) => {
        stripePaymentMethod = event.value.type;
        stripePaymentMethodIsCreditCard = event.value.type === 'card';
    };

    return (
        <PaymentElement
            onChange={handleOnChange}
            options={{
                fields: {
                    billingDetails: {
                        name: 'never',
                        email: 'never',
                    },
                },
            }}
        />
    );
};

let appearanceOptions = {};

interface StripeSettings extends GatewaySettings {
    formId: number;
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

/**
 * @since 3.18.0 added fields conditional when donation amount is zero
 * @since 3.13.0 Use only stripeKey to load the Stripe script (when stripeConnectedAccountId is missing) to prevent errors when the account is connected through API keys
 * @since 3.12.1 updated afterCreatePayment response type to include billing details address
 * @since 3.0.0
 */
const stripePaymentElementGateway: StripeGateway = {
    id: 'stripe_payment_element',
    initialize() {
        const {stripeKey, stripeConnectedAccountId, formId} = this.settings;

        if (!stripeKey && !stripeConnectedAccountId) {
            throw new Error('Stripe gateway settings are missing.  Check your Stripe settings.');
        }

        appearanceOptions = applyFilters('givewp_stripe_payment_element_appearance_options', {}, formId) as object;

        /**
         * Create the Stripe object and pass our api keys
         * @see https://stripe.com/docs/payments/accept-a-payment-deferred
         */
        stripePromise = loadStripe(
            stripeKey,
            stripeConnectedAccountId
                ? {
                      stripeAccount: stripeConnectedAccountId,
                  }
                : {}
        );
    },
    beforeCreatePayment: async function (values): Promise<object> {
        if (!this.stripe || !this.elements) {
            // Stripe.js has not yet loaded.
            // Make sure to disable form submission until Stripe.js has loaded.
            throw new Error('Stripe was not able to load.');
        }

        // Trigger form validation and wallet collection
        const {error: submitError} = await this.elements.submit();

        if (submitError) {
            let errorMessage = __('Invalid Payment Data.', 'give');

            if (typeof submitError === 'string') {
                errorMessage = sprintf(__('Invalid Payment Data. Error Details: %s', 'give'), submitError);
            }

            if (submitError.hasOwnProperty('code') && submitError.hasOwnProperty('message')) {
                errorMessage = sprintf(
                    __('Invalid Payment Data. Error Details: %s (code: %s)', 'give'),
                    submitError.message,
                    submitError.code
                );
            }

            throw new Error(errorMessage);
        }

        return {
            stripePaymentMethod,
            stripePaymentMethodIsCreditCard,
            ...this.settings,
        };
    },
    afterCreatePayment: async function (response: {
        data: {
            clientSecret: string;
            returnUrl: string;
            billingDetails: {
                name: string;
                email: string;
                address?: {
                    city?: string;
                    country?: string;
                    line1?: string;
                    line2?: string;
                    postal_code?: string;
                    state?: string;
                };
            };
        };
    }): Promise<void> {
        const {error} = await this.stripe.confirmPayment({
            elements: this.elements,
            clientSecret: response.data.clientSecret,
            confirmParams: {
                payment_method_data: {
                    billing_details: response.data.billingDetails,
                },
                return_url: response.data.returnUrl,
            },
        });

        console.error(error);
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
        if (!stripePromise) {
            throw new Error('Stripe library was not able to load.  Check your Stripe settings.');
        }

        const {useWatch} = window.givewp.form.hooks;
        const donationType = useWatch({name: 'donationType'});
        const donationCurrency = useWatch({name: 'currency'});
        const donationAmount = useWatch({name: 'amount'});
        const stripeAmount = dollarsToCents(donationAmount, donationCurrency.toString().toUpperCase());

        const stripeElementOptions: StripeElementsOptionsMode = {
            mode: donationType === 'subscription' ? 'subscription' : 'payment',
            amount: stripeAmount,
            currency: donationCurrency.toLowerCase(),
            appearance: appearanceOptions,
        };

        if (donationAmount <= 0) {
            return <>{__('Donation amount must be greater than zero to proceed.', 'give')}</>;
        }

        return (
            <Elements stripe={stripePromise} options={stripeElementOptions}>
                <StripeFields gateway={stripePaymentElementGateway} />
            </Elements>
        );
    },
};

window.givewp.gateways.register(stripePaymentElementGateway);
