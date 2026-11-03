import {__, _n, sprintf} from '@wordpress/i18n';
import {datePresetLabel, parseDateToken} from './dateRange';
import type {BarFilterDeclaration, FilterValue} from './types';

/**
 * The most labels a chip lists. With more, it shows how many are selected.
 *
 * @since TBD
 */
export const MAX_CHIP_LABELS = 3;

/**
 * The label of an entity the client has no name for (yet): "Donor #123".
 *
 * @since TBD
 */
export function fallbackLabel(declaration: BarFilterDeclaration, id: string): string {
    /* translators: 1: filter name such as "Donor", 2: record id. */
    return sprintf(__('%1$s #%2$s', 'give'), declaration.label, id);
}

/**
 * The text of a date value: a preset name, a range or one open end.
 */
function dateText(value: string, formatDate: (ymd: string) => string): string {
    const range = parseDateToken(value);

    if (!range) {
        return value;
    }

    if ('preset' in range) {
        return datePresetLabel(range.preset);
    }

    if (range.start && range.end) {
        /* translators: 1: first date, 2: last date. */
        return sprintf(__('%1$s – %2$s', 'give'), formatDate(range.start), formatDate(range.end));
    }

    if (range.start) {
        /* translators: %s: a date. */
        return sprintf(__('on or after %s', 'give'), formatDate(range.start));
    }

    /* translators: %s: a date. */
    return sprintf(__('on or before %s', 'give'), formatDate(range.end ?? ''));
}

/**
 * The chip text of a filter. `text` is what the chip shows; `fullText` lists every label and is
 * used for the tooltip and the accessible name.
 *
 * @since TBD
 */
export function filterChipText(
    declaration: BarFilterDeclaration,
    value: FilterValue | undefined,
    helpers: {labelOf: (value: string) => string; formatDate: (ymd: string) => string}
): {text: string; fullText: string} {
    const none = {text: declaration.label, fullText: declaration.label};

    if (value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
        return none;
    }

    /* translators: 1: filter name such as "Status", 2: the chosen values. */
    const withLabel = (values: string) => sprintf(__('%1$s: %2$s', 'give'), declaration.label, values);

    if (declaration.kind === 'date') {
        const text = withLabel(dateText(String(value), helpers.formatDate));

        return {text, fullText: text};
    }

    const labelOf = (item: string) =>
        helpers.labelOf(item) || (declaration.kind === 'entity' ? fallbackLabel(declaration, item) : item);
    const labels = (Array.isArray(value) ? value : [value]).map(labelOf);
    const fullText = withLabel(labels.join(', '));

    if (labels.length <= MAX_CHIP_LABELS) {
        return {text: fullText, fullText};
    }

    /* translators: %d: number of selected values. */
    const count = sprintf(_n('%d selected', '%d selected', labels.length, 'give'), labels.length);

    return {text: withLabel(count), fullText};
}
