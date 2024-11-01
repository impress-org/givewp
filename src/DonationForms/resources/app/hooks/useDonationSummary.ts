import {
    DonationSummaryLineItem, DonationTotals,
    useDonationSummaryContext,
    useDonationSummaryDispatch
} from '@givewp/forms/app/store/donation-summary';
import {
    addAmountToTotal,
    addItem,
    removeAmountFromTotal,
    removeItem
} from '@givewp/forms/app/store/donation-summary/reducer';
import {useCallback} from '@wordpress/element';
import type {
    subscriptionPeriod
} from '@givewp/forms/registrars/templates/groups/DonationAmount/subscriptionPeriod';

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
const dollarsToCents = (amount: string, currency: string) => {
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
const getDonationTotal = (totals: DonationTotals, amount: number) =>
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
}
/**
 * The donation summary hook is used to interact with the donation summary context which wraps around our donation form.
 * It provides methods to add and remove items from the summary, as well as to add and remove amounts from the total.
 * It also provides the current items and totals from the context, making it easier to access form values specific to donations.
 *
 * Although the initial intent for this hook was to be used in the DonationSummary component for visual reasons, it is also recommended to be used in others
 * areas like gateways to get the total donation amount and currency.
 *
 * @unreleased added currency, donationAmountBase, donationAmountTotal
 * @since 3.0.0
 */
export default function useDonationSummary() {
    const { items, totals } = useDonationSummaryContext();
    const dispatch = useDonationSummaryDispatch();
    const { useWatch } = window.givewp.form.hooks;

    const amount = useWatch({ name: 'amount' }) as string;
    const currency = useWatch({ name: 'currency' }) as string;
    const period = useWatch({name: 'subscriptionPeriod'}) as subscriptionPeriod | undefined;
    const frequency = useWatch({name: 'subscriptionFrequency'}) as number | undefined;
    const donationType = useWatch({name: 'donationType'}) as "single" | "subscription" | undefined;

    const donationAmountTotal = getDonationTotal(totals, Number(amount));
    const subscriptionAmount = getSubscriptionTotal(totals, Number(amount))

    return {
        items,
        totals,
        addItem: useCallback((item: DonationSummaryLineItem) => dispatch(addItem(item)), [dispatch]),
        removeItem: useCallback((itemId: string) => dispatch(removeItem(itemId)), [dispatch]),
        addToTotal: useCallback(
            (itemId: string, amount: number) => dispatch(addAmountToTotal(itemId, amount)),
            [dispatch]
        ),
        removeFromTotal: useCallback((itemId: string) => dispatch(removeAmountFromTotal(itemId)), [dispatch]),
        state: {
            currency,
            donationAmount: Number(amount),
            donationAmountMinor: dollarsToCents(amount, currency),
            donationAmountTotal,
            donationAmountTotalMinor: dollarsToCents(donationAmountTotal.toString(), currency),
            subscriptionAmount,
            subscriptionAmountMinor: dollarsToCents(subscriptionAmount.toString(), currency),
            donationIsOneTime: donationType === 'single',
            donationIsRecurring: donationType === 'subscription',
            subscriptionPeriod: period,
            subscriptionFrequency: frequency,
        },
    };
}
