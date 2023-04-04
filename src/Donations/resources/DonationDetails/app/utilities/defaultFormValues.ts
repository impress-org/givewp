/**
 *
 * @unreleased
 */

const {
    id,
    status,
    formId,
    donorId,
    feeAmountRecovered,
    createdAt,
    amount,
    billingAddress
} = window.GiveDonations.donationDetails

export const defaultFormValues: {
    id: number;
    amount: number;
    feeAmountRecovered: number;
    createdAt: Date;
    status: string;
    formId: number;
    donorId: number;
    country: string;
    address1: string;
    address2: string;
    city: string;
    state: string;
    zip: string;
} = {
    id: id,
    amount: amount.value,
    feeAmountRecovered: feeAmountRecovered?.value || 0,
    createdAt: new Date(createdAt.date),
    status: status,
    formId: formId,
    donorId: donorId,
    country: billingAddress.country,
    address1: billingAddress.address1,
    address2: billingAddress.address2,
    city: billingAddress.city,
    state: billingAddress.state,
    zip: billingAddress.zip,
};
