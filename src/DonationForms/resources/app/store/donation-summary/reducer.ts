import {DonationSummaryLineItem} from '@givewp/forms/app/store/donation-summary/index';

const ADD_ITEM = 'add_item';
const REMOVE_ITEM = 'remove_item';
const SHOW_ITEM = 'show_item';
const HIDE_ITEM = 'hide_item';
const ADD_AMOUNT_TO_TOTAL = 'add_amount_to_total';
const REMOVE_AMOUNT_FROM_TOTAL = 'remove_amount_from_total';

/**
 * @unreleased Added SHOW_ITEM and HIDE_ITEM actions
 * @since 3.0.0
 */
export default function reducer(draft, action) {
    switch (action.type) {
        case ADD_ITEM:
            draft.items[action.item.id] = action.item;
            break;

        case REMOVE_ITEM:
            delete draft.items[action.itemId];
            break;

        case SHOW_ITEM:
            draft.items[action.itemId].visible = true;
            break;

        case HIDE_ITEM:
            draft.items[action.itemId].visible = false;
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
 * @since 3.0.0
 */
export function addItem(item: DonationSummaryLineItem) {
    return {
        type: ADD_ITEM,
        item,
    };
}

/**
 * @since 3.0.0
 */
export function removeItem(itemId: string) {
    return {
        type: REMOVE_ITEM,
        itemId,
    };
}

/**
 * @unreleased
 */
export function showItem(itemId: string) {
    return {
        type: SHOW_ITEM,
        itemId,
    };
}

/**
 * @unreleased
 */
export function hideItem(itemId: string) {
    return {
        type: HIDE_ITEM,
        itemId,
    };
}

/**
 * @since 3.0.0
 */
export function addAmountToTotal(itemId: string, amount: number) {
    return {
        type: ADD_AMOUNT_TO_TOTAL,
        itemId,
        amount,
    };
}

/**
 * @since 3.0.0
 */
export function removeAmountFromTotal(itemId: string) {
    return {
        type: REMOVE_AMOUNT_FROM_TOTAL,
        itemId,
    };
}
