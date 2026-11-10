import {dateToken, parseDateToken} from './dateRange';
import {NONE_VALUE} from './filterKinds';
import type {
    BarFilterDeclaration,
    EntityFilterDeclaration,
    FilterValue,
    OptionsFilterDeclaration,
    ParsedFilterValue,
    StatusVocabularyEntry,
} from './types';

/**
 * The most values one filter keeps. More are cut off.
 *
 * @since TBD
 */
export const MAX_FILTER_VALUES = 50;

const TOKEN_PATTERN = /^[A-Za-z0-9_-]{1,64}$/;
const ID_PATTERN = /^[1-9]\d{0,19}$/;

/**
 * The values an options filter offers.
 */
function offeredValues(declaration: OptionsFilterDeclaration, statuses: StatusVocabularyEntry[]): string[] {
    if (declaration.options === 'statuses') {
        return statuses.filter((entry) => entry.filterable).map((entry) => entry.value);
    }

    return declaration.options.map((option) => option.value);
}

/**
 * The values that cannot be combined with any other.
 */
function exclusiveValuesOf(declaration: OptionsFilterDeclaration | EntityFilterDeclaration): string[] {
    if (declaration.kind === 'entity') {
        return declaration.allowNone ? [NONE_VALUE] : [];
    }

    return declaration.exclusiveValues ?? [];
}

/**
 * The value as plain text for the URL, or undefined when the filter holds nothing.
 *
 * @since TBD
 */
export function serializeFilterValue(declaration: BarFilterDeclaration, value: FilterValue): string | undefined {
    const text = Array.isArray(value) ? value.slice(0, MAX_FILTER_VALUES).join(',') : value;

    if (declaration.kind === 'date' && text !== '') {
        const range = parseDateToken(text);

        return range ? dateToken(range) : undefined;
    }

    return text === '' ? undefined : text;
}

/**
 * Reads the plain text of a filter. Invalid parts are dropped and `corrected` says the address must
 * be rewritten. `parse(serialize(value))` gives back every valid value.
 *
 * @since TBD
 */
export function parseFilterValue(
    declaration: BarFilterDeclaration,
    text: string,
    context: {statuses: StatusVocabularyEntry[]}
): ParsedFilterValue {
    if (declaration.kind === 'date') {
        const range = parseDateToken(text);

        return range ? {value: dateToken(range), corrected: dateToken(range) !== text} : {corrected: true};
    }

    const rawParts = text.split(',');
    const offered = declaration.kind === 'options' ? offeredValues(declaration, context.statuses) : undefined;
    const exclusive = exclusiveValuesOf(declaration);

    const isValid = (part: string): boolean => {
        // The declaration names what an options filter offers, so that list decides, whatever characters a value has.
        if (offered) {
            return offered.includes(part);
        }

        return TOKEN_PATTERN.test(part) && (ID_PATTERN.test(part) || (exclusive.includes(part) && part === NONE_VALUE));
    };

    const valid = rawParts.filter(isValid);
    const unique = valid.filter((part, index) => valid.indexOf(part) === index);
    let corrected = unique.length !== rawParts.length;

    if (unique.length === 0) {
        return {corrected: true};
    }

    if (!declaration.multiple) {
        return {value: unique[0], corrected: corrected || unique.length > 1};
    }

    const exclusiveValue = unique.find((part) => exclusive.includes(part));

    if (exclusiveValue !== undefined) {
        return {value: [exclusiveValue], corrected: corrected || unique.length > 1};
    }

    if (unique.length > MAX_FILTER_VALUES) {
        corrected = true;
    }

    return {value: unique.slice(0, MAX_FILTER_VALUES), corrected};
}
