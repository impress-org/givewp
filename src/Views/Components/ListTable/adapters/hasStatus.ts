import type {ListTableParameters} from '../ListTablePage/types';

/**
 * Whether the list is filtered to `status`. The status parameter is one value on some screens and
 * a list on others.
 *
 * @since TBD
 */
export function hasStatus(parameters: ListTableParameters | undefined, status: string): boolean {
    return ([] as unknown[])
        .concat(parameters?.status ?? [])
        .map(String)
        .includes(status);
}
