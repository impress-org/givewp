import {DonationSummaryLineItem} from '@givewp/forms/app/store/donation-summary/index';

const ADD_ITEM = 'add_item';
const REMOVE_ITEM = 'remove_item';
const ADD_AMOUNT_TO_TOTAL = 'add_amount_to_total';
const REMOVE_AMOUNT_FROM_TOTAL = 'remove_amount_from_total';

/**
 * @unreleased
 */
export default function reducer(draft, action) {
    switch (action.type) {
        case ADD_ITEM:
            draft.items[action.item.id] = action.item;
            break;

        case REMOVE_ITEM:
            delete draft.items[action.itemId];
            break;

        case ADD_AMOUNT_TO_TOTAL:
            draft.totals[action.itemId] = action.amount;
            break;

        case REMOVE_AMOUNT_FROM_TOTAL:
            delete draft.totals[action.itemId];
            break;
        default:
            break;
    }
}

/**
 * @unreleased
 */
export function addItem(item: DonationSummaryLineItem) {
    return {
        type: ADD_ITEM,
        item,
    };
}

/**
 * @unreleased
 */
export function addAmountToTotal(itemId: string, amount: number) {
    return {
        type: ADD_AMOUNT_TO_TOTAL,
        itemId,
        amount,
    };
}

/**
 * @unreleased
 */
export function removeAmountFromTotal(itemId: string) {
    return {
        type: REMOVE_AMOUNT_FROM_TOTAL,
        itemId,
    };
}

/**
 * @unreleased
 */
export function removeItem(itemId: string) {
    return {
        type: REMOVE_ITEM,
        itemId,
    };
}
