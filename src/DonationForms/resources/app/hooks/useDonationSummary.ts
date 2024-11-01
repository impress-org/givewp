import {
    DonationSummaryLineItem,
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
 * @unreleased
 */
const getDonationTotal = (totals: any, amount: any) =>
    Number(
        Object.values({
            ...totals,
            amount: Number(amount),
        }).reduce((total: number, amount: number) => {
            return total + amount;
        }, 0)
    );

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
    const amount = useWatch({ name: 'amount' });
    const currency = useWatch({ name: 'currency' });
    const period = useWatch({name: 'subscriptionPeriod'});
    const frequency = useWatch({name: 'subscriptionFrequency'});
    const donationType = useWatch({name: 'donationType'});

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
            donationAmountBase: Number(amount),
            donationAmountBaseMinor: dollarsToCents(amount, currency),
            donationAmountTotal: getDonationTotal(totals, amount),
            donationAmountTotalMinor: dollarsToCents(getDonationTotal(totals, amount).toString(), currency),
            donationIsOneTime: donationType === 'single',
            donationIsRecurring: donationType === 'subscription',
            subscriptionPeriod: period,
            subscriptionFrequency: frequency,
        },
    };
}
