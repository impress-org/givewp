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
    const {currency} = getDonationOptionsWindowData();
    const exchangeRate = Number(donation?.exchangeRate ?? 1);
    const donationAmountValue = Number(donation?.amount?.value ?? 0);
    const intendedAmountValue = Number(donation?.amount?.value ?? 0) - Number(donation?.feeAmountRecovered?.value ?? 0);
    const feeAmountRecoveredValue = Number(donation?.feeAmountRecovered?.value ?? 0);
    // @ts-ignore
    const eventTicketValue = Number(donation?.eventTicketAmount?.value ?? 0);

    return {
        currency,
        formatter: amountFormatter(currency),
        amount: normalizeAmount(donationAmountValue, exchangeRate),
        intendedAmount: normalizeAmount(intendedAmountValue, exchangeRate),
        feeAmountRecovered: normalizeAmount(feeAmountRecoveredValue, exchangeRate),
        eventTicketAmount: normalizeAmount(eventTicketValue, exchangeRate),
    };
}
