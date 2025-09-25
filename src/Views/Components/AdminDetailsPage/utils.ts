import { SchemaProperty } from "./types";

/**
 * @since 4.4.0
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}

/**
 * @since 4.6.0
 */
export function formatDateTimeLocal(dateString: string) {
    if (!dateString) return '';

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}:00`;
}


/**
 * @since 4.8.0
 */
export function formatDateLocal(dateString: string) {
    if (!dateString) return '';

    const date = new Date(dateString);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

/**
 * @unreleased
 */
export function filterOutReadOnlyFields(
    record: Record<string, any>,
    schemaProperties: Record<string, SchemaProperty>
): Record<string, any> {
    const processValue = (value: any, schema: SchemaProperty): any => {
        // Handle nested objects
        if (schema.properties && typeof value === "object" && value !== null && !Array.isArray(value)) {
            return filterOutReadOnlyFields(value, schema.properties as Record<string, SchemaProperty>);
        }

        // Handle arrays of objects
        if (schema.type === "array" && schema.items && Array.isArray(value)) {
            return value.map(item =>
                typeof item === "object" && item !== null
                    ? filterOutReadOnlyFields(item, (schema.items as any).properties ?? {})
                    : item
            );
        }

        return value;
    };

    return Object.fromEntries(
        Object.entries(record)
            .filter(([key]) => !schemaProperties[key]?.readOnly && !schemaProperties[key]?.readonly)
            .map(([key, value]) => [
                key,
                schemaProperties[key] ? processValue(value, schemaProperties[key]) : value
            ])
    );
}
