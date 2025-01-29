import type {Gateway} from '@givewp/forms/types';
import PayPalCardFields from './resources/js/PayPalCardFields';
import type {PayPalCommerceGateway, PayPalCommerceGatewaySettings} from './types';
import handleSubmit from './resources/js/PayPalCardFields/handleSubmit';

// @ts-ignore
let payPalDonationsSettings: PayPalCommerceGatewaySettings = [];

const payPalCommerceGateway: PayPalCommerceGateway = {
    id: 'paypal-commerce',
    initialize() {
        payPalDonationsSettings = this.settings;
    },
    beforeCreatePayment: async function (values): Promise<object> {
        if (!this.cardFieldsForm) {
            return new Error('PayPal Card Fields form is not available.');
        }

        try {
            await handleSubmit(this.cardFieldsForm);

            if (!this.payPalOrderId) {
                return new Error('PayPal Order ID is not available.');
            }

            return {
                paymentMethod: 'card-fields',
                payPalOrderId: this.payPalOrderId,
            };

        } catch (err) {
            console.error(err);
            return new Error('Error submitting PayPal Card Fields form.');
        }
    },
    Fields() {
        return (
            <PayPalCardFields
                clientId={payPalDonationsSettings.sdkOptions['client-id']}
                gateway={payPalCommerceGateway}
            />
        );
    },
};

window.givewp.gateways.register(payPalCommerceGateway);
