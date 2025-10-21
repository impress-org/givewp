import { useState, useMemo } from 'react';
import { FilterByGroupedOptions } from '../types';

/**
 * Custom hook to manage filter state and visibility logic
 *
 * @since 4.12.0
 */
export function useFilterState(
    groupedOptions: FilterByGroupedOptions[],
    initialValues?: Record<string, string[]>
) {
    // Initialize filters from props
    const [selectedFilters, setSelectedFilters] = useState<Record<string, string[]>>(() => {
        const newSelectedFilters: Record<string, string[]> = {};
        groupedOptions.forEach((group) => {
            newSelectedFilters[group.apiParam] = initialValues?.[group.apiParam] || [];
        });
        return newSelectedFilters;
    });

    const visibleGroups = useMemo(() => {
        // Calculate visible groups based on current filter state
        const visible = groupedOptions.filter((group) => {
            if (group.isVisible) {
                return group.isVisible(selectedFilters);
            }
            return true;
        });

        // Clean up filters for invisible groups
        const invisibleGroups = groupedOptions.filter(
            (group) => !visible.includes(group)
        );

        if (invisibleGroups.length > 0) {
            setSelectedFilters((prev) => {
                const newFilters = { ...prev };
                let hasChanges = false;

                invisibleGroups.forEach((group) => {
                    const groupOptionValues = group.options.map((opt) => opt.value);
                    const currentValues = newFilters[group.apiParam] || [];
                    const filteredValues = currentValues.filter(
                        (value) => !groupOptionValues.includes(value)
                    );

                    if (filteredValues.length !== currentValues.length) {
                        newFilters[group.apiParam] = filteredValues;
                        hasChanges = true;
                    }
                });

                return hasChanges ? newFilters : prev;
            });
        }

        return visible;
    }, [selectedFilters]);

    return { selectedFilters, setSelectedFilters, visibleGroups };
}
