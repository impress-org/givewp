import {__} from '@wordpress/i18n';
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

/**
 * Shifts a `YYYY-MM-DD` day by whole days, counting on UTC dates so the browser zone never matters.
 */
function addDays(day: string, days: number): string {
    const date = new Date(`${day}T00:00:00Z`);
    date.setUTCDate(date.getUTCDate() + days);

    return date.toISOString().slice(0, 10);
}

/**
 * The first day of the month that is `monthsAgo` months before the month of `today`.
 */
function startOfMonth(today: string, monthsAgo: number): string {
    const date = new Date(`${today.slice(0, 7)}-01T00:00:00Z`);
    date.setUTCMonth(date.getUTCMonth() - monthsAgo);

    return date.toISOString().slice(0, 10);
}

/**
 * The days a date value covers, counted from `today` (a `YYYY-MM-DD` day in the site's calendar).
 * A range with one end stays open on the other end.
 *
 * @since TBD
 */
export function resolveDateRange(value: DateRangeValue, today: string): {start?: string; end?: string} {
    if (!('preset' in value)) {
        return {...(value.start && {start: value.start}), ...(value.end && {end: value.end})};
    }

    switch (value.preset) {
        case '7':
        case '30':
        case '90':
            return {start: addDays(today, 1 - Number(value.preset)), end: today};
        case 'this-month':
            return {start: startOfMonth(today, 0), end: today};
        case 'last-month':
            return {start: startOfMonth(today, 1), end: addDays(startOfMonth(today, 0), -1)};
        case 'this-year':
            return {start: `${today.slice(0, 4)}-01-01`, end: today};
    }
}

/**
 * Whether both days are set and the end comes before the start.
 *
 * @since TBD
 */
export function isEndBeforeStart(start?: string, end?: string): boolean {
    return Boolean(start && end && end < start);
}

/**
 * The request bounds of a range: the whole start day and the whole end day, without an offset, so
 * the server reads them as site time.
 *
 * @since TBD
 */
export function dayBounds(range: {start?: string; end?: string}): {start?: string; end?: string} {
    return {
        ...(range.start && {start: `${range.start}T00:00:00`}),
        ...(range.end && {end: `${range.end}T23:59:59`}),
    };
}

/**
 * The translated name of a preset.
 *
 * @since TBD
 */
export function datePresetLabel(preset: DatePreset): string {
    switch (preset) {
        case '7':
            return __('Last 7 days', 'give');
        case '30':
            return __('Last 30 days', 'give');
        case '90':
            return __('Last 90 days', 'give');
        case 'this-month':
            return __('This month', 'give');
        case 'last-month':
            return __('Last month', 'give');
        case 'this-year':
            return __('This year', 'give');
    }
}
