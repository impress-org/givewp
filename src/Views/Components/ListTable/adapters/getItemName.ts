import type {ListTableItem} from '../ListTablePage/types';

/**
 * The columns that hold a row's name, in the order they are looked for: Donations has `donor`,
 * Donors has `donorInformation`, the other screens have `title`.
 *
 * @since TBD
 */
export const NAME_COLUMN_IDS = ['donor', 'title', 'donorInformation'];

/**
 * The name of a row, as the confirm dialogs list it. Same rule as the old table's checkbox names,
 * so the screens' confirm bodies receive the names they always did (cell HTML, rendered with
 * Interweave). Falls back to the id.
 *
 * @since TBD
 */
export function getItemName(item: ListTableItem): string {
    const name = NAME_COLUMN_IDS.map((id) => item[id]).find((value) => value !== undefined && value !== null);

    return name === undefined || name === null || name === '' ? String(item.id) : String(name);
}
