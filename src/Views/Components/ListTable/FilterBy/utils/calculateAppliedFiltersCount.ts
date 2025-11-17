import { FilterByGroupedOptions } from '../types';

/**
 * Calculate the total count of applied filters
 *
 * @since 4.12.0
 */
export function calculateAppliedFiltersCount(
    groupedOptions: FilterByGroupedOptions[],
    values?: Record<string, string[]>
): number {
    if (!values) return 0;

    const uniqueApiParams = Array.from(
        new Set(groupedOptions.map((group) => group.apiParam))
    );

    return uniqueApiParams.reduce((total, apiParam) => {
        const groupValues = values[apiParam];
        return total + (groupValues ? groupValues.length : 0);
    }, 0);
}
