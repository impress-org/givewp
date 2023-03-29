import {parseAmountValue} from './formatter';

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
    amount: parseAmountValue(amount.value),
    feeAmountRecovered: parseAmountValue(feeAmountRecovered?.value),
    createdAt: new Date(createdAt.date),
    status: status,
    formId: formId,
};
