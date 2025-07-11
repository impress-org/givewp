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
    const {currency: baseCurrency, eventTicketsEnabled} = getDonationOptionsWindowData();
    const exchangeRate = Number(donation?.exchangeRate ?? 1);
    const donationAmountValue = Number(donation?.amount?.value ?? 0);
    const feeAmountRecoveredValue = Number(donation?.feeAmountRecovered?.value ?? 0);
    const eventTicketsAmountValue = Number(donation?.eventTicketsAmount?.value ?? 0);
    const intendedAmountValue = (donationAmountValue - feeAmountRecoveredValue) - (eventTicketsEnabled ? eventTicketsAmountValue : 0);
    const currency = donation?.amount?.currency ?? baseCurrency;

    return {
        baseCurrency,
        formatter: amountFormatter(currency),
        amount: donationAmountValue,
        intendedAmount: intendedAmountValue,
        feeAmountRecovered: feeAmountRecoveredValue,
        eventTicketsAmount: eventTicketsAmountValue,
    };
}
