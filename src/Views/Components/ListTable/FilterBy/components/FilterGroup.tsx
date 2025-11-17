import styles from '../styles.module.scss';
import { FilterByGroupedOptions } from '../types';
import { FilterOption } from './FilterOption';

interface FilterGroupProps {
    group: FilterByGroupedOptions;
    selectedFilters: Record<string, string[]>;
    onCheckboxChange: (apiParam: string, value: string) => void;
    onRadioChange: (apiParam: string, value: string) => void;
    onRadioClick: (apiParam: string, value: string) => void;
}

/**
 * @since 4.12.0
 */
export function FilterGroup({
    group,
    selectedFilters,
    onCheckboxChange,
    onRadioChange,
    onRadioClick,
}: FilterGroupProps) {
    return (
        <div key={group.id} className={styles.filterGroup}>
            {group.showTitle !== false && <h3 className={styles.filterGroupTitle}>{group.name}</h3>}
            <div className={styles[`filterGroupOptions--${group.type}`]}>
                {group.options.map((option) => {
                    const isChecked = selectedFilters[group.apiParam]?.includes(option.value) || false;

                    return (
                        <FilterOption
                            key={option.value}
                            group={group}
                            option={option}
                            isChecked={isChecked}
                            onCheckboxChange={onCheckboxChange}
                            onRadioChange={onRadioChange}
                            onRadioClick={onRadioClick}
                        />
                    );
                })}
            </div>
        </div>
    );
}
