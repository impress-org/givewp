/**
 * @since 4.12.0
 */
export interface FilterByProps {
    groupedOptions: FilterByGroupedOptions[];
    onChange: (key: string, values: string[]) => void;
    values?: Record<string, string[]>;
}

/**
 * @since 4.12.0
 */
export interface FilterByGroupedOptions {
    id: string;
    name: string;
    apiParam: string;
    type: 'checkbox' | 'radio' | 'toggle';
    showTitle?: boolean;
    options: FilterOption[];
    isVisible?: (values: Record<string, string[]>) => boolean;
}

/**
 * @since 4.12.0
 */
export interface FilterOption {
    value: string;
    text: string;
}
