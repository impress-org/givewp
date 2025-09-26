/**
 * Schema property interface for filtering read-only fields
 *
 * @unreleased
 */
export interface SchemaProperty {
    readOnly?: boolean;
    properties?: Record<string, SchemaProperty>;
    [key: string]: any;
}
