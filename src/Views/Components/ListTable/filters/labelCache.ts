import type {FilterEntitySource, FilterOption} from './types';

const labels = new Map<string, string>();
const requested = new Set<string>();

const keyOf = (source: FilterEntitySource, value: string) => `${source}:${value}`;

/**
 * Keeps the labels of options that were loaded, so a chip can name an id that is not in the list.
 *
 * @since TBD
 */
export function rememberLabels(source: FilterEntitySource, options: FilterOption[]): void {
    options.forEach((option) => labels.set(keyOf(source, option.value), option.label));
}

/**
 * @since TBD
 */
export function knownLabel(source: FilterEntitySource, value: string): string | undefined {
    return labels.get(keyOf(source, value));
}

/**
 * Marks ids as asked for. Returns the ones that were not asked for before and have no label.
 *
 * @since TBD
 */
export function claimUnknownValues(source: FilterEntitySource, values: string[]): string[] {
    const claimed = Array.from(new Set(values)).filter((value) => {
        const key = keyOf(source, value);

        return !labels.has(key) && !requested.has(key);
    });
    claimed.forEach((value) => requested.add(keyOf(source, value)));

    return claimed;
}

/**
 * Lets ids be asked for again, after a failed request.
 *
 * @since TBD
 */
export function releaseValues(source: FilterEntitySource, values: string[]): void {
    values.forEach((value) => requested.delete(keyOf(source, value)));
}

/**
 * Empties the cache. For tests.
 *
 * @since TBD
 */
export function resetLabelCache(): void {
    labels.clear();
    requested.clear();
}
