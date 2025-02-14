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
 * The donation summary hook is used to interact with the donation summary context which wraps around our donation form.
 * It provides methods to add and remove items from the summary, as well as to add and remove amounts from the total.
 * It also provides the current items and totals from the context, making it easier to access form values specific to donations.
 *
 * @unreleased added getTotalSum
 * @since 3.0.0
 */
export default function useDonationSummary() {
    const { items, totals } = useDonationSummaryContext();
    const dispatch = useDonationSummaryDispatch();

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
        getTotalSum: useCallback((amount: number) =>
            Number(
                Object.values({
                    ...totals,
                    amount
                }).reduce((total: number, amount: number) => {
                    return total + amount;
                }, 0)
            ), [totals])
    };
}
