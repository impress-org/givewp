import type {ListTableItem} from '../ListTablePage/types';

/**
 * The items an action may run on. DataViews checks `isEligible` before it opens an action's modal,
 * but calls a `callback` with every selected row, so a callback filters them itself.
 *
 * @since TBD
 */
export function onlyEligible(
    items: ListTableItem[],
    isEligible: ((item: ListTableItem) => boolean) | undefined
): ListTableItem[] {
    return isEligible ? items.filter(isEligible) : items;
}
