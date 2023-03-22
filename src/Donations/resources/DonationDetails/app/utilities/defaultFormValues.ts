import {data} from '../config/data';
import {parseAmountValue} from './formatter';

/**
 *
 * @unreleased
 */
export const defaultFormValues: {
    amount: number;
    feeAmountRecovered: number;
    createdAt: string;
    status: string;
    form: number;
} = {
    amount: parseAmountValue(data.amount.value),
    feeAmountRecovered: parseAmountValue(data.feeAmountRecovered),
    createdAt: data.createdAt.date,
    status: data.status,
    form: data.formId,
};
