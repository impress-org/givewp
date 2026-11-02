import {useEffect, useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import {Button, __experimentalInputControl as InputControl} from '@wordpress/components';
import {DATE_PRESETS, datePresetLabel, isEndBeforeStart} from '../filters/dateRange';
import type {DateRangeValue} from '../filters/types';
import './styles.scss';

/**
 * @since TBD
 */
export interface DateRangeFilterControlProps {
    /** Names the group for assistive technology. */
    label: string;
    value?: DateRangeValue;
    /** Shows a `YYYY-MM-DD` day in the site's date format. */
    formatDate: (ymd: string) => string;
    /** A preset, a range with one or two days, or undefined to clear. */
    onChange: (value: DateRangeValue | undefined) => void;
}

const customDays = (value?: DateRangeValue) => {
    if (!value || 'preset' in value) {
        return {start: '', end: ''};
    }

    return {start: value.start ?? '', end: value.end ?? ''};
};

/**
 * The popover content of a date filter: six preset buttons, and a start and an end day with Apply.
 * An end before the start is announced and cannot be applied.
 *
 * @since TBD
 */
export default function DateRangeFilterControl({label, value, formatDate, onChange}: DateRangeFilterControlProps) {
    const [start, setStart] = useState(customDays(value).start);
    const [end, setEnd] = useState(customDays(value).end);

    useEffect(() => {
        setStart(customDays(value).start);
        setEnd(customDays(value).end);
    }, [value]);

    const invalid = isEndBeforeStart(start, end);
    const chosenPreset = value && 'preset' in value ? value.preset : undefined;

    const apply = () => {
        if (invalid) {
            return;
        }

        if (!start && !end) {
            onChange(undefined);
            return;
        }

        onChange({...(start && {start}), ...(end && {end})});
    };

    const summary = () => {
        if (start && end) {
            return sprintf(
                /* translators: 1: first day, 2: last day, both in the site's date format. */
                __('%1$s to %2$s', 'give'),
                formatDate(start),
                formatDate(end)
            );
        }

        if (start) {
            /* translators: %s: first day in the site's date format. */
            return sprintf(__('From %s', 'give'), formatDate(start));
        }

        /* translators: %s: last day in the site's date format. */
        return sprintf(__('Until %s', 'give'), formatDate(end));
    };

    return (
        <div role="group" aria-label={label} className="givewp-date-range-filter-control">
            <div className="givewp-date-range-filter-control__presets">
                {DATE_PRESETS.map((preset) => (
                    <Button
                        key={preset}
                        variant="tertiary"
                        className="givewp-date-range-filter-control__preset"
                        isPressed={chosenPreset === preset}
                        onClick={() => onChange({preset})}
                    >
                        {datePresetLabel(preset)}
                    </Button>
                ))}
            </div>
            <div className="givewp-date-range-filter-control__days">
                <InputControl
                    type="date"
                    label={__('Start date', 'give')}
                    value={start}
                    onChange={(next?: string) => setStart(next ?? '')}
                />
                <InputControl
                    type="date"
                    label={__('End date', 'give')}
                    value={end}
                    onChange={(next?: string) => setEnd(next ?? '')}
                />
            </div>
            {(start || end) && !invalid && <p className="givewp-date-range-filter-control__summary">{summary()}</p>}
            {invalid && (
                <p role="alert" className="givewp-date-range-filter-control__error">
                    {__('The end date must be on or after the start date.', 'give')}
                </p>
            )}
            <Button
                variant="primary"
                className="givewp-date-range-filter-control__apply"
                disabled={invalid}
                onClick={apply}
            >
                {__('Apply', 'give')}
            </Button>
        </div>
    );
}
