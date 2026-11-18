import type {ListTableItem} from '../ListTablePage/types';

/**
 * The ids of the items, comma-separated, as the list action endpoints take them.
 *
 * @since TBD
 */
export const idsOf = (items: ListTableItem[]): string => items.map((item) => item.id).join(',');
