import type {DonationTotals} from '@givewp/forms/app/store/donation-summary';
import {useDonationSummaryContext} from '@givewp/forms/app/store/donation-summary';
import type {subscriptionPeriod} from '@givewp/forms/registrars/templates/groups/DonationAmount/subscriptionPeriod';

/**
 * Zero decimal currencies are currencies that do not have a minor unit.
 * For example, the Japanese Yen (JPY) does not have a minor unit.
 * @unreleased
 *
 * @see https://stripe.com/docs/currencies#zero-decimal
 */
const zeroDecimalCurrencies = [
    'BIF',
    'CLP',
    'DJF',
    'GNF',
    'JPY',
    'KMF',
    'KRW',
    'MGA',
    'PYG',
    'RWF',
    'UGX',
    'VND',
    'VUV',
    'XAF',
    'XOF',
    'XPF',
];

/**
 * Takes in an amount value in dollar units and returns the calculated cents (minor) amount
 *
 * @unreleased
 */
const amountToMinorUnit = (amount: string, currency: string) => {
    if (zeroDecimalCurrencies.includes(currency)) {
        return Math.round(parseFloat(amount));
    }

    return Math.round(parseFloat(amount) * 100);
};

/**
 * Donation total calculation
 *
 * @unreleased
 */
const getAmountTotal = (totals: DonationTotals, amount: number) =>
    Number(
        Object.values({
            ...totals,
            amount,
        }).reduce((total: number, amount: number) => {
            return total + amount;
        }, 0)
    );

/**
 * Subscription total calculation
 * TODO: figure out which totals will be included in subscriptions
 *
 * @unreleased
 */
const getSubscriptionTotal = (totals: DonationTotals, amount: number) => {
    let total = 0;

    // Subscriptions currently only support donation amount (TODO: and potentially feeRecovery values)
    const allowedKeys = ['feeRecovery'];

    for (const [key, value] of Object.entries(totals)) {
        if (allowedKeys.includes(key)) {
            total += value;
        }
    }

    return Number(total + amount);
};

/**
 * @unreleased
 */
export default function useFormData() {
    const {totals} = useDonationSummaryContext();
    const {useWatch} = window.givewp.form.hooks;

    const firstName = useWatch({name: 'firstName'}) as string;
    const lastName = useWatch({name: 'lastName'}) as string | undefined;
    const email = useWatch({name: 'email'}) as string;
    const billingAddress = {
        addressLine1: useWatch({name: 'address1'}) as string | undefined,
        addressLine2: useWatch({name: 'address2'}) as string | undefined,
        city: useWatch({name: 'city'}) as string | undefined,
        state: useWatch({name: 'state'}) as string | undefined,
        postalCode: useWatch({name: 'zip'}) as string | undefined,
        country: useWatch({name: 'country'}) as string | undefined,
    };
    const amount = useWatch({name: 'amount'}) as string;
    const currency = useWatch({name: 'currency'}) as string;
    const subscriptionPeriod = useWatch({name: 'subscriptionPeriod'}) as subscriptionPeriod | undefined;
    const subscriptionFrequency = useWatch({name: 'subscriptionFrequency'}) as number | undefined;
    const subscriptionInstallments = useWatch({name: 'subscriptionInstallments'});
    const donationType = useWatch({name: 'donationType'}) as 'single' | 'subscription' | undefined;

    const amountTotal = getAmountTotal(totals, Number(amount));
    const amountTotalInMinorUnits = amountToMinorUnit(amountTotal.toString(), currency);
    const subscriptionAmountTotal = getSubscriptionTotal(totals, Number(amount));
    const subscriptionAmountTotalInMinorUnits = amountToMinorUnit(subscriptionAmountTotal.toString(), currency);

    const donationAmount = Number(amount);
    const donationAmountMinor = amountToMinorUnit(amount, currency);

    const isOneTime = donationType === 'single';
    const isRecurring = donationType === 'subscription';

    return {
        firstName,
        lastName,
        email,
        currency,
        billingAddress,
        amount: isOneTime ? amountTotal : subscriptionAmountTotal,
        amountInMinorUnits: isOneTime ? amountTotalInMinorUnits : subscriptionAmountTotalInMinorUnits,
        isOneTime,
        isRecurring,
        subscriptionPeriod,
        subscriptionFrequency,
        subscriptionInstallments,
    };
}
