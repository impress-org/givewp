import {amountFormatter} from '@givewp/admin/utils';
import { Subscription } from '../admin/components/types';
import { getSubscriptionOptionsWindowData } from '../utils';

/**
 * This hook is used to get the subscription amounts and the formatter for the subscription amounts.
 *
 * @since 4.8.0
 */
export default function useSubscriptionAmounts(subscription: Subscription) {
    const {currency: baseCurrency} = getSubscriptionOptionsWindowData();
    const subscriptionAmountValue = Number(subscription?.amount?.value ?? 0);
    const feeAmountRecoveredValue = Number(subscription?.feeAmountRecovered?.value ?? 0);
    const intendedAmountValue = (subscriptionAmountValue - feeAmountRecoveredValue);
    const currency = subscription?.amount?.currency ?? baseCurrency;

    return {
        baseCurrency,
        formatter: amountFormatter(currency),
        amount: subscriptionAmountValue,
        intendedAmount: intendedAmountValue,
        feeAmountRecovered: feeAmountRecoveredValue,
    };
}
