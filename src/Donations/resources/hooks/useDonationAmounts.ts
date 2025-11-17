import { Donation } from '@givewp/donations/admin/components/types';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';
import {amountFormatter} from '@givewp/admin/utils';

/**
 * This hook is used to get the donation amounts and the formatter for the donation amounts.
 * @since 4.6.0
 */
export default function useDonationAmounts(donation: Donation) {
    const {currency: baseCurrency, eventTicketsEnabled} = getDonationOptionsWindowData();
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
