import type {FilterEntitySource, FilterOption} from './types';

const labels = new Map<string, string>();
const requested = new Set<string>();
/** Goes up each time a source gets a new or changed label. */
const versions = new Map<FilterEntitySource, number>();
const listeners = new Set<() => void>();

const keyOf = (source: FilterEntitySource, value: string) => `${source}:${value}`;

/**
 * Keeps the labels of options that were loaded, so a chip can name an id that is not in the list.
 * Tells every subscriber when a label is new or changed.
 *
 * @since TBD
 */
export function rememberLabels(source: FilterEntitySource, options: FilterOption[]): void {
    let changed = false;
    options.forEach((option) => {
        const key = keyOf(source, option.value);
        if (labels.get(key) !== option.label) {
            labels.set(key, option.label);
            changed = true;
        }
    });

    if (changed) {
        versions.set(source, labelsVersion(source) + 1);
        listeners.forEach((listener) => listener());
    }
}

/**
 * Calls the listener each time a label is added or changed. Returns a function that stops it.
 *
 * @since TBD
 */
export function subscribeToLabels(listener: () => void): () => void {
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
}

/**
 * A number that changes each time the labels of a source change.
 *
 * @since TBD
 */
export function labelsVersion(source: FilterEntitySource): number {
    return versions.get(source) ?? 0;
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
    versions.clear();
}
