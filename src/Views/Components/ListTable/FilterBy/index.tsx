import { useMemo } from 'react';
import { __ } from '@wordpress/i18n';
import styles from './styles.module.scss';
import { FilterByProps } from './types';
import { useFilterState } from './hooks/useFilterState';
import { useDropdownToggle } from './hooks/useDropdownToggle';
import { calculateAppliedFiltersCount } from './utils/calculateAppliedFiltersCount';
import { FilterGroup } from './components/FilterGroup';
import FilterByIcon from './Icon';

/**
 * @since 4.12.0
 */
export default function FilterBy({ groupedOptions, onChange, values }: FilterByProps) {
    const { isOpen, setIsOpen, dropdownRef } = useDropdownToggle();
    const { selectedFilters, setSelectedFilters, visibleGroups } = useFilterState(
        groupedOptions,
        values
    );

    const appliedFiltersCount = useMemo(
        () => calculateAppliedFiltersCount(groupedOptions, values),
        [groupedOptions, values]
    );

    const handleToggle = () => setIsOpen(!isOpen);

    const handleCheckboxChange = (apiParam: string, optionValue: string) => {
        setSelectedFilters((prev) => {
            const groupValues = prev[apiParam] || [];
            const newGroupValues = groupValues.includes(optionValue)
                ? groupValues.filter((v) => v !== optionValue)
                : [...groupValues, optionValue];

            return { ...prev, [apiParam]: newGroupValues };
        });
    };

    const handleRadioChange = (apiParam: string, optionValue: string) => {
        setSelectedFilters((prev) => ({
            ...prev,
            [apiParam]: [optionValue],
        }));
    };

    const handleRadioClick = (apiParam: string, optionValue: string) => {
        const groupValues = selectedFilters[apiParam] || [];
        const isCurrentlySelected = groupValues.includes(optionValue);

        if (isCurrentlySelected) {
            setSelectedFilters((prev) => ({
                ...prev,
                [apiParam]: [],
            }));
        }
    };

    const handleReset = () => {
        setSelectedFilters((prev) => {
            const resetFilters = { ...prev };
            visibleGroups.forEach((group) => {
                resetFilters[group.apiParam] = [];
            });
            return resetFilters;
        });
    };

    const handleApply = () => {
        Object.entries(selectedFilters).forEach(([apiParam, values]) => {
            onChange(apiParam, values);
        });
        setIsOpen(false);
    };

    return (
        <div className={styles.filterBy} ref={dropdownRef}>
            <button
                type="button"
                className={styles.filterByButton}
                onClick={handleToggle}
                aria-expanded={isOpen}
                aria-haspopup="true"
            >
                {__('Filter by', 'give')}
                {appliedFiltersCount > 0 && (
                    <span className={styles.badge}>{appliedFiltersCount}</span>
                )}
                <FilterByIcon />
            </button>

            {isOpen && (
                <div className={styles.dropdown}>
                    <div className={styles.dropdownContent}>
                        {visibleGroups.map((group) => (
                            <FilterGroup
                                key={group.id}
                                group={group}
                                selectedFilters={selectedFilters}
                                onCheckboxChange={handleCheckboxChange}
                                onRadioChange={handleRadioChange}
                                onRadioClick={handleRadioClick}
                            />
                        ))}
                    </div>

                    <div className={styles.dropdownActions}>
                        <button
                            type="button"
                            className={styles.resetButton}
                            onClick={handleReset}
                        >
                            {__('Reset', 'give')}
                        </button>
                        <button
                            type="button"
                            className={styles.applyButton}
                            onClick={handleApply}
                        >
                            {__('Apply', 'give')}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
