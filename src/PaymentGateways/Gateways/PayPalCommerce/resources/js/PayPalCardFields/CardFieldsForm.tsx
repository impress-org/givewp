import {
    PayPalCardFieldsForm,
    usePayPalCardFields
} from '@paypal/react-paypal-js';
import {PayPalCommerceGateway} from '../../../types';

export default function CardFieldsForm({gateway}: {gateway: PayPalCommerceGateway}) {
     const {cardFieldsForm} = usePayPalCardFields();
     gateway.cardFieldsForm = cardFieldsForm;

    return <PayPalCardFieldsForm />
}
