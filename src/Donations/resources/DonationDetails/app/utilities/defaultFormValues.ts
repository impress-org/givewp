/**
 *
 * @unreleased
 */

const {id, status, formId, feeAmountRecovered, createdAt, amount} = window.GiveDonations.donationDetails

export const defaultFormValues: {
    id: number;
    amount: number;
    feeAmountRecovered: number;
    createdAt: Date;
    status: string;
    formId: number;
} = {
    id: id,
    amount: amount.value,
    feeAmountRecovered: feeAmountRecovered?.value || 0,
    createdAt: new Date(createdAt.date),
    status: status,
    formId: formId,
};
