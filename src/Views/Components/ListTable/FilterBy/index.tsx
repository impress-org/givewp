import {useState, useRef, useEffect} from 'react';
import {__} from '@wordpress/i18n';
import styles from './styles.module.scss';
import {FilterByGroupedOptions} from '@givewp/components/ListTable/ListTablePage';

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

    useEffect(() => {
        const newSelectedFilters: Record<string, string[]> = {};

        groupedOptions.forEach((group) => {
            newSelectedFilters[group.id] = values?.[group.id] || [];
        });

        setSelectedFilters(newSelectedFilters);
    }, [values, groupedOptions]);

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

    const handleCheckboxChange = (groupId: string, optionValue: string) => {
        setSelectedFilters((prev) => {
            const groupValues = prev[groupId] || [];
            const newGroupValues = groupValues.includes(optionValue)
                ? groupValues.filter((v) => v !== optionValue)
                : [...groupValues, optionValue];

            return {
                ...prev,
                [groupId]: newGroupValues,
            };
        });
    };

    const handleRadioChange = (groupId: string, optionValue: string) => {
        setSelectedFilters((prev) => {
            return {
                ...prev,
                [groupId]: [optionValue],
            };
        });
    };

    const handleRadioClick = (groupId: string, optionValue: string) => {
        const groupValues = selectedFilters[groupId] || [];
        const isCurrentlySelected = groupValues.includes(optionValue);

        if (isCurrentlySelected) {
            setSelectedFilters((prev) => {
                return {
                    ...prev,
                    [groupId]: [],
                };
            });
        }
    };

    const handleReset = () => {
        setSelectedFilters((prev) => {
            const resetFilters = { ...prev };

            groupedOptions.forEach((group) => {
                resetFilters[group.id] = [];
            });

            return resetFilters;
        });
    };

    const handleApply = () => {
        Object.entries(selectedFilters).forEach(([key, values]) => {
            onChange(key, values);
        });
        setIsOpen(false);
    };

    const getSelectedCount = () => {
        return Object.values(selectedFilters).reduce((total, group) => total + group.length, 0);
    };

    const selectedCount = getSelectedCount();

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
                {selectedCount > 0 && <span className={styles.badge}>{selectedCount}</span>}
                <svg
                    className={styles.chevron}
                    width="10"
                    height="6"
                    viewBox="0 0 10 6"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M1 1L5 5L9 1"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </button>

            {isOpen && (
                <div className={styles.dropdown}>
                    <div className={styles.dropdownContent}>
                        {groupedOptions.map((group: FilterByGroupedOptions) => (
                            <div key={group.id} className={styles.filterGroup}>
                                <h3 className={styles.filterGroupTitle}>{group.name}</h3>
                                <div className={styles.filterGroupOptions}>
                                    {group.options.map((option) => {
                                        const inputId = `${group.id}-${option.value}`;
                                        const isChecked =
                                            selectedFilters[group.id]?.includes(option.value) || false;

                                        return (
                                            <label
                                                key={option.value}
                                                htmlFor={inputId}
                                                className={styles.filterOption}
                                            >
                                                <input
                                                    type={group.type}
                                                    id={inputId}
                                                    name={group.id}
                                                    value={option.value}
                                                    checked={isChecked}
                                                    onChange={() =>
                                                        group.type === 'checkbox'
                                                            ? handleCheckboxChange(group.id, option.value)
                                                            : handleRadioChange(group.id, option.value)
                                                    }
                                                    onClick={() => group.type === 'radio' && handleRadioClick(group.id, option.value)}
                                                    className={styles.filterInput}
                                                />
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
