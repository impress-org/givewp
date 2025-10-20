import { useState, useEffect, useMemo } from 'react';
import { FilterByGroupedOptions } from '../types';

/**
 * Custom hook to manage filter state and visibility logic
 *
 * @unreleased
 */
export function useFilterState(
    groupedOptions: FilterByGroupedOptions[],
    initialValues?: Record<string, string[]>
) {
    const [selectedFilters, setSelectedFilters] = useState<Record<string, string[]>>({});

    // Initialize filters from props
    useEffect(() => {
        const newSelectedFilters: Record<string, string[]> = {};
        groupedOptions.forEach((group) => {
            newSelectedFilters[group.apiParam] = initialValues?.[group.apiParam] || [];
        });
        setSelectedFilters(newSelectedFilters);
    }, []);

    // Calculate visible groups based on current filter state
    const visibleGroups = useMemo(() => {
        return groupedOptions.filter((group) => {
            if (group.isVisible) {
                return group.isVisible(selectedFilters);
            }
            return true;
        });
    }, [groupedOptions, selectedFilters]);

    // Clean up filters for invisible groups
    useEffect(() => {
        const invisibleGroups = groupedOptions.filter(
            (group) => !visibleGroups.includes(group)
        );

        if (invisibleGroups.length > 0) {
            setSelectedFilters((prev) => {
                const newFilters = { ...prev };

                invisibleGroups.forEach((group) => {
                    const groupOptionValues = group.options.map((opt) => opt.value);
                    const currentValues = newFilters[group.apiParam] || [];

                    newFilters[group.apiParam] = currentValues.filter(
                        (value) => !groupOptionValues.includes(value)
                    );
                });

                return newFilters;
            });
        }
    }, [visibleGroups, groupedOptions]);

    return { selectedFilters, setSelectedFilters, visibleGroups };
}
