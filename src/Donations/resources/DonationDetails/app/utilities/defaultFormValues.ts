import {data} from '../config/data';
import {parseAmountValue} from './formatter';

/**
 *
 * @unreleased
 */
export const defaultFormValues: {
    amount: number;
    feeAmountRecovered: number;
    createdAt: string | Date;
    status: string;
    form: number;
} = {
    amount: parseAmountValue(data.amount.value),
    feeAmountRecovered: parseAmountValue(data.feeAmountRecovered),
    createdAt: new Date(data.createdAt.date).toISOString(),
    status: data.status,
    form: data.formId,
};
