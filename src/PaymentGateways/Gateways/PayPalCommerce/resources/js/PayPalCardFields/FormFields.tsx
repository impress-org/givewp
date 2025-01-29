import {
    PayPalCardFieldsForm,
    usePayPalCardFields
} from '@paypal/react-paypal-js';
import {PayPalCommerceGateway} from '../../../types';

export default function FormFields({gateway}: {gateway: PayPalCommerceGateway}) {
     const {cardFieldsForm} = usePayPalCardFields();
     gateway.cardFieldsForm = cardFieldsForm;

    return <PayPalCardFieldsForm />
}
