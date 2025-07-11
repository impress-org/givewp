import { Donation } from '@givewp/donations/admin/components/types';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import {amountFormatter} from '@givewp/src/Admin/utils';

/**
 * @unreleased
 */
const normalizeAmount = (amount: number, exchangeRate: number = 1) => {
    return amount / exchangeRate;
}

/**
 * @unreleased
 */
export function useNormalizeDonation(donation: Donation) {
    const {currency: baseCurrency} = getDonationOptionsWindowData();
    const exchangeRate = Number(donation?.exchangeRate ?? 1);
    const donationAmountValue = Number(donation?.amount?.value ?? 0);
    const feeAmountRecoveredValue = Number(donation?.feeAmountRecovered?.value ?? 0);
    // @ts-ignore
    const eventTicketsAmountValue = Number(donation?.eventTicketAmount?.value ?? 0);
    const intendedAmountValue = donationAmountValue - feeAmountRecoveredValue + eventTicketsAmountValue;
    const currency = donation?.amount?.currency ?? baseCurrency;

    return {
        baseCurrency,
        formatter: amountFormatter(currency),
        amount: donationAmountValue,
        intendedAmount: intendedAmountValue,
        feeAmountRecovered: feeAmountRecoveredValue,
        eventTicketAmount: eventTicketsAmountValue,
    };
}
