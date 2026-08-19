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

/**
 * @since 4.11.0
 */
export interface SelectOption {
    value: number;
    label: string;
    record?: any;
}

/**
 * @since 4.11.0
 */
export interface UseAsyncSelectOptionReturn {
    selectedOption: SelectOption | null;
    loadOptions: (searchInput: string) => Promise<{
        options: SelectOption[];
        hasMore: boolean;
    }>;
    mapOptionsForMenu: (options: SelectOption[]) => SelectOption[];
    error: Error | null;
}
