import type {BarFilterDeclaration, FilterDeclaration} from './types';

/**
 * The value of the "No campaign" choice. It is sent to the server as the text `none`.
 *
 * @since TBD
 */
export const NONE_VALUE = 'none';

/**
 * Whether a declaration is a filter in the bar, that is, anything but the search box.
 *
 * @since TBD
 */
export const isBarFilter = (declaration: FilterDeclaration): declaration is BarFilterDeclaration =>
    declaration.kind !== 'search';
