import PayPalCardFields from './resources/js/PayPalCardFields';
import {isUsingCardFields, isUsingSmartButtons, PayPalCommerceGateway, PayPalCommerceGatewaySettings} from './types';
import {PayPalScriptProvider} from '@paypal/react-paypal-js';
import PayPalSmartButtons from './resources/js/PayPalSmartButtons';

// @ts-ignore
let payPalDonationsSettings: PayPalCommerceGatewaySettings = [];

const payPalCommerceGateway: PayPalCommerceGateway = {
    id: 'paypal-commerce',
    initialize() {
        payPalDonationsSettings = this.settings;
    },
    beforeCreatePayment: async function (values): Promise<object> {
        if (isUsingSmartButtons(this)) {
            if (!this.payPalOrderId || !this.payPalAuthorizationId) {
                // trigger smart buttons
                await this.paypalOnClickActions.resolve();
            } else {
                return {
                    paymentMethod: 'buttons',
                    payPalOrderId: this.payPalOrderId,
                    payPalAuthorizationId: this.payPalAuthorizationId,
                };
            }
        } else if (isUsingCardFields(this)) {
            if (!this.cardFieldsForm) {
                return new Error('PayPal Card Fields form is not available.');
            }

            try {
                const cardFormState = await this.cardFieldsForm.getState();

                if (!cardFormState.isFormValid) {
                    return new Error('PayPal Card Fields form is invalid');
                }

                console.log('Card Fields submitting...');

                await this.cardFieldsForm.submit();

                if (!this.payPalOrderId) {
                    return new Error('PayPal Order ID is not available.');
                }

                return {
                    paymentMethod: 'card-fields',
                    payPalOrderId: this.payPalOrderId,
                    payPalAuthorizationId: this.payPalAuthorizationId,
                };
            } catch (err) {
                console.error(err);
                return new Error('Error submitting PayPal Card Fields form.');
            }
        } else {
            return new Error('PayPal is not available.');
        }
    },
    Fields() {
        const components = ['card-fields', 'buttons'];
        return (
            <PayPalScriptProvider
                options={{
                    clientId: payPalDonationsSettings.sdkOptions['client-id'],
                    components,
                    intent: 'authorize',
                }}
            >
                {components.includes('card-fields') && <PayPalCardFields gateway={payPalCommerceGateway} />}
                {components.includes('buttons') && <PayPalSmartButtons gateway={payPalCommerceGateway} />}
            </PayPalScriptProvider>
        );
    },
};

window.givewp.gateways.register(payPalCommerceGateway);
