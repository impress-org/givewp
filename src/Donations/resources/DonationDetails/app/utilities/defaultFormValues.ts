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
    createdAt: string | Date;
    status: string;
    form: number;
} = {
    id: id,
    amount: parseAmountValue(amount.value),
    feeAmountRecovered: parseAmountValue(feeAmountRecovered),
    createdAt: new Date(createdAt.date).toISOString(),
    status: status,
    form: formId,
};
