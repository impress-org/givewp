/**
 * Schema property interface for filtering read-only fields
 *
 * @since 4.10.0
 */
export interface SchemaProperty {
    readOnly?: boolean;
    properties?: Record<string, SchemaProperty>;
    [key: string]: any;
}
