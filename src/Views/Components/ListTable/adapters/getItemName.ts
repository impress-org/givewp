import type {ListTableItem} from '../ListTablePage/types';

/**
 * The name of a row, as the confirm dialogs list it. Same rule as the old table's checkbox names,
 * so the screens' confirm bodies receive the names they always did (cell HTML, rendered with
 * Interweave). Falls back to the id.
 *
 * @since TBD
 */
export function getItemName(item: ListTableItem): string {
    const name = item.donor ?? item.title ?? item.donorInformation;

    return name === undefined || name === null || name === '' ? String(item.id) : String(name);
}
