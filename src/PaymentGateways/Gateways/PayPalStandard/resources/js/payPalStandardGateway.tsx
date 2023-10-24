import type {Gateway} from '@givewp/forms/types';
import PayPalLogo from './PayPalLogo';

type PayPalStandardGatewaySettings = {
    fields: {
        heading: string;
        subheading: string;
        body: string;
    };
};

let fields: PayPalStandardGatewaySettings['fields'];

const paypalStandardGateway: Gateway = {
    id: 'paypal',
    initialize() {
        fields = this.settings.fields;
    },
    Fields() {
        return (
            <div style={{
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                rowGap: "1rem",
                textAlign: "center"
            }}>
                <PayPalLogo/>
                <b>{fields.heading}</b>
                <div><b>{fields.subheading}:</b> {fields.body}</div>
            </div>
        );
    }
}

window.givewp.gateways.register(paypalStandardGateway);