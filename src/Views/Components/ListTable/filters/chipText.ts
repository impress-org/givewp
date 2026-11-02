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
 * The chip text of a filter. `text` is what the chip shows; `fullText` is the filter name with every
 * label; `accessibleName` (the chip's name and tooltip) starts with `text`, so the spoken name holds
 * the visible words, and then lists the labels when the chip shows only a count. When every label is a numbered fallback such as
 * "Donor #123", the filter name is not repeated in front of it.
 *
 * @since TBD Leave out the filter name when every label is a numbered fallback.
 * @since TBD Add the accessible name, which starts with the chip text.
 * @since TBD
 */
export function filterChipText(
    declaration: BarFilterDeclaration,
    value: FilterValue | undefined,
    helpers: {labelOf: (value: string) => string; formatDate: (ymd: string) => string}
): {text: string; fullText: string; accessibleName: string} {
    const same = (text: string) => ({text, fullText: text, accessibleName: text});

    if (value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
        return same(declaration.label);
    }

    /* translators: 1: filter name such as "Status", 2: the chosen values. */
    const withLabel = (values: string) => sprintf(__('%1$s: %2$s', 'give'), declaration.label, values);

    if (declaration.kind === 'date') {
        return same(withLabel(dateText(String(value), helpers.formatDate)));
    }

    const labelOf = (item: string) =>
        helpers.labelOf(item) || (declaration.kind === 'entity' ? fallbackLabel(declaration, item) : item);
    const labels = (Array.isArray(value) ? value : [value]).map(labelOf);
    const list = labels.join(', ');

    if (labels.length <= MAX_CHIP_LABELS) {
        // "Donor #123" already names the filter, so "Donor: Donor #123" would say it twice.
        const fallbackStart = fallbackLabel(declaration, '');
        const allFallback = labels.every((label) => label.startsWith(fallbackStart));

        return same(allFallback ? list : withLabel(list));
    }

    /* translators: %d: number of selected values. */
    const count = sprintf(_n('%d selected', '%d selected', labels.length, 'give'), labels.length);

    const text = withLabel(count);

    return {
        text,
        fullText: withLabel(list),
        /* translators: 1: the chip text such as "Status: 4 selected", 2: the chosen values. */
        accessibleName: sprintf(__('%1$s. %2$s', 'give'), text, list),
    };
}
