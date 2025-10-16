/**
 * @unreleased
 */
export interface FilterByProps {
    groupedOptions: FilterByGroupedOptions[];
    onChange: (key: string, values: string[]) => void;
    values?: Record<string, string[]>;
}

/**
 * @unreleased
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
 * @unreleased
 */
export interface FilterOption {
    value: string;
    text: string;
}
