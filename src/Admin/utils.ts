import {formatDistanceToNow} from 'date-fns';
import { SchemaProperty } from './types';

/**
 * @since 4.0.0
 */
export function amountFormatter(
    currency: Intl.NumberFormatOptions['currency'],
    options?: Intl.NumberFormatOptions
): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options,
    });
}

/**
 * @since unreleased
 */
export function formatTimestamp(timestamp: string | null | undefined, useComma: boolean = false): string {
    // Handle null, undefined, or empty string
    if (!timestamp) {
        return '—';
    }

    const date = new Date(timestamp);

    // Check if the date is valid
    if (isNaN(date.getTime())) {
        return '—';
    }

    const day = date.getDate();
    const ordinal = (day: number): string => {
        if (day > 3 && day < 21) return 'th';
        switch (day % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    };

    const dayWithOrdinal = `${day}${ordinal(day)}`;
    const month = date.toLocaleString('en-US', {month: 'long'});
    const year = date.getFullYear();
    const time = date.toLocaleString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true}).toLowerCase();
    const separator = useComma ? ', ' : ' • ';

    return `${dayWithOrdinal} ${month} ${year}${separator}${time}`;
}

/**
 * Returns a relative time string for a given date (e.g. "Today" or "2 days ago")
 *
 * @since unreleased
 */
export function getRelativeTimeString(date: Date): string {
    const now = new Date();
    if (date.toDateString() === now.toDateString()) {
        return 'Today';
    }
    return formatDistanceToNow(date, {addSuffix: true});
}

/**
 * @unreleased
 */
export function prepareDefaultValuesFromSchema(
    record: Record<string, any>,
    schemaProperties: Record<string, SchemaProperty>
): Record<string, any> {
    const isReadOnly = (schema: SchemaProperty): boolean => {
        return schema?.readOnly || schema?.readonly;
    };

    const isObject = (value: any, schema: SchemaProperty): boolean => {
        return schema?.properties &&typeof value === "object" && value !== null && !Array.isArray(value);
    };

    const isArray = (value: any, schema: SchemaProperty): boolean => {
        return schema?.type === "array" && schema?.items && Array.isArray(value);
    };

    const processValue = (value: any, schema: SchemaProperty): any => {
        if (isObject(value, schema)) {
            return prepareDefaultValuesFromSchema(value, schema.properties as Record<string, SchemaProperty>);
        }

        if (isArray(value, schema)) {
            return value.map(item =>
                isObject(item, schema.items as SchemaProperty)
                    ? prepareDefaultValuesFromSchema(item, (schema.items as any).properties ?? {})
                    : item
            );
        }

        return value;
    };

    return Object.fromEntries(
        Object.entries(record)
            .filter(([key]) => !isReadOnly(schemaProperties[key]))
            .map(([key, value]) => [
                key,
                schemaProperties[key]
                    ? processValue(value, schemaProperties[key])
                    : value
            ])
    );
}
