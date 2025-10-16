import {useState, useRef, useEffect, useMemo} from 'react';
import {__} from '@wordpress/i18n';
import styles from './styles.module.scss';
import {FilterByGroupedOptions} from '@givewp/components/ListTable/ListTablePage';
import FilterByIcon from './Icon';

/**
 * @unreleased
 */
interface FilterByProps {
    groupedOptions: FilterByGroupedOptions[];
    onChange: (key: string, values: string[]) => void;
    values?: Record<string, string[]>;
}

/**
 * @unreleased
 */
export default function FilterBy({groupedOptions, onChange, values}: FilterByProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [selectedFilters, setSelectedFilters] = useState<Record<string, string[]>>({});
    const dropdownRef = useRef<HTMLDivElement>(null);

    const isGroupVisible = (group: FilterByGroupedOptions): boolean => {
        if (group.isVisible) {
            return group.isVisible(selectedFilters);
        }

        return true;
    };

    const visibleGroups = useMemo(() => {
        return groupedOptions.filter(isGroupVisible);
    }, [groupedOptions, selectedFilters]);

    useEffect(() => {
        const invisibleGroups = groupedOptions.filter(group => !isGroupVisible(group));

        if (invisibleGroups.length > 0) {
            setSelectedFilters((prev) => {
                const newFilters = { ...prev };

                invisibleGroups.forEach((group) => {
                    const groupOptionValues = group.options.map(opt => opt.value);
                    const currentValues = newFilters[group.apiParam] || [];

                    newFilters[group.apiParam] = currentValues.filter(
                        value => !groupOptionValues.includes(value)
                    );
                });

                return newFilters;
            });
        }
    }, [visibleGroups, groupedOptions]);

    const appliedFiltersCount = useMemo(() => {
        if (!values) {
            return 0;
        }

        const uniqueApiParams = Array.from(new Set(groupedOptions.map(group => group.apiParam)));

        return uniqueApiParams.reduce((total, apiParam) => {
            const groupValues = values[apiParam];
            return total + (groupValues ? groupValues.length : 0);
        }, 0);
    }, [JSON.stringify(values)]);

    useEffect(() => {
        const newSelectedFilters: Record<string, string[]> = {};
        groupedOptions.forEach((group) => {
            newSelectedFilters[group.apiParam] = values?.[group.apiParam] || [];
        });
        setSelectedFilters(newSelectedFilters);
    }, []);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isOpen]);

    const handleToggle = () => {
        setIsOpen(!isOpen);
    };

    const handleCheckboxChange = (apiParam: string, optionValue: string) => {
        setSelectedFilters((prev) => {
            const groupValues = prev[apiParam] || [];
            const newGroupValues = groupValues.includes(optionValue)
                ? groupValues.filter((v) => v !== optionValue)
                : [...groupValues, optionValue];

            return {
                ...prev,
                [apiParam]: newGroupValues,
            };
        });
    };

    const handleRadioChange = (apiParam: string, optionValue: string) => {
        setSelectedFilters((prev) => {
            return {
                ...prev,
                [apiParam]: [optionValue],
            };
        });
    };

    const handleRadioClick = (apiParam: string, optionValue: string) => {
        const groupValues = selectedFilters[apiParam] || [];
        const isCurrentlySelected = groupValues.includes(optionValue);

        if (isCurrentlySelected) {
            setSelectedFilters((prev) => {
                return {
                    ...prev,
                    [apiParam]: [],
                };
            });
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
                {appliedFiltersCount > 0 && <span className={styles.badge}>{appliedFiltersCount}</span>}
                <FilterByIcon />
            </button>

            {isOpen && (
                <div className={styles.dropdown}>
                    <div className={styles.dropdownContent}>
                        {visibleGroups.map((group: FilterByGroupedOptions) => (
                            <div key={group.id} className={styles.filterGroup}>
                                {group.showTitle !== false && <h3 className={styles.filterGroupTitle}>{group.name}</h3>}
                                <div className={styles[`filterGroupOptions--${group.type}`]}>
                                    {group.options.map((option) => {
                                        const inputId = `${group.id}-${option.value}`;
                                        const isChecked =
                                            selectedFilters[group.apiParam]?.includes(option.value) || false;

                                        return (
                                            <label
                                                key={option.value}
                                                htmlFor={inputId}
                                                className={styles.filterOption}
                                            >
                                                <input
                                                    type={group.type === 'toggle' ? 'checkbox' : group.type}
                                                    id={inputId}
                                                    name={group.id}
                                                    value={option.value}
                                                    checked={isChecked}
                                                    onChange={() =>
                                                        group.type === 'checkbox' || group.type === 'toggle'
                                                            ? handleCheckboxChange(group.apiParam, option.value)
                                                            : handleRadioChange(group.apiParam, option.value)
                                                    }
                                                    onClick={() => group.type === 'radio' && handleRadioClick(group.apiParam, option.value)}
                                                    className={styles.filterInput}
                                                />
                                                {group.type === 'toggle' && (
                                                    <span className={styles.filterToggleSlider} />
                                                )}
                                                <span className={styles.filterLabel}>{option.text}</span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </div>
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
