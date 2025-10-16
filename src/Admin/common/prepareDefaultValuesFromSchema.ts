import { SchemaProperty } from '../types';

/**
 * @since 4.10.0
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
