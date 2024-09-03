import {
    DonationSummaryLineItem,
    useDonationSummaryContext,
    useDonationSummaryDispatch,
} from '@givewp/forms/app/store/donation-summary';
import {
    addAmountToTotal,
    addItem,
    hideItem,
    removeAmountFromTotal,
    removeItem,
    showItem,
} from '@givewp/forms/app/store/donation-summary/reducer';
import {useCallback} from '@wordpress/element';

/**
 * @unreleased Added showItem and hideItem methods
 * @since 3.0.0
 */
export default function useDonationSummary() {
    const {items, totals} = useDonationSummaryContext();
    const dispatch = useDonationSummaryDispatch();

    return {
        items,
        totals,
        addItem: useCallback((item: DonationSummaryLineItem) => dispatch(addItem(item)), [dispatch]),
        removeItem: useCallback((itemId: string) => dispatch(removeItem(itemId)), [dispatch]),
        showItem: useCallback((itemId: string) => dispatch(showItem(itemId)), [dispatch]),
        hideItem: useCallback((itemId: string) => dispatch(hideItem(itemId)), [dispatch]),
        addToTotal: useCallback(
            (itemId: string, amount: number) => dispatch(addAmountToTotal(itemId, amount)),
            [dispatch]
        ),
        removeFromTotal: useCallback((itemId: string) => dispatch(removeAmountFromTotal(itemId)), [dispatch]),
    };
}
