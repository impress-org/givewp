import {DATE_PRESETS} from './types';
import type {DatePreset, DateRangeValue} from './types';

export {DATE_PRESETS};

const DAY_PATTERN = /^\d{4}-\d{2}-\d{2}$/;

/**
 * Whether the text is a real calendar day written as `YYYY-MM-DD`.
 */
function isRealDay(day: string): boolean {
    if (!DAY_PATTERN.test(day)) {
        return false;
    }

    const date = new Date(`${day}T00:00:00Z`);

    return !Number.isNaN(date.getTime()) && date.toISOString().slice(0, 10) === day;
}

/**
 * Reads the text of a date filter: a preset, or `<day>_<day>`, `<day>_` or `_<day>` with real days
 * and the start not after the end. Anything else gives undefined.
 *
 * @since TBD
 */
export function parseDateToken(token: string): DateRangeValue | undefined {
    if ((DATE_PRESETS as readonly string[]).includes(token)) {
        return {preset: token as DatePreset};
    }

    const parts = token.split('_');

    if (parts.length !== 2) {
        return undefined;
    }

    const [start, end] = parts;

    if (!start && !end) {
        return undefined;
    }

    if ((start && !isRealDay(start)) || (end && !isRealDay(end))) {
        return undefined;
    }

    if (start && end && start > end) {
        return undefined;
    }

    return {...(start && {start}), ...(end && {end})};
}

/**
 * The text of a date filter value. The opposite of `parseDateToken`.
 *
 * @since TBD
 */
export function dateToken(value: DateRangeValue): string {
    if ('preset' in value) {
        return value.preset;
    }

    return `${value.start ?? ''}_${value.end ?? ''}`;
}
