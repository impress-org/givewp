import type {PayPalCardFieldsComponent} from '@paypal/paypal-js';

export default async function handleSubmit(
    cardFieldsForm: PayPalCardFieldsComponent
) {
    const formState = await cardFieldsForm.getState();

    if (!formState.isFormValid) {
        return alert('The payment form is invalid');
    }

    console.log("Card Fields submitting...");

    return await cardFieldsForm.submit();
}
