import styles from '../styles.module.scss';
import { FilterByGroupedOptions, FilterOption as FilterOptionType } from '../types';

interface FilterOptionProps {
    group: FilterByGroupedOptions;
    option: FilterOptionType;
    isChecked: boolean;
    onCheckboxChange: (apiParam: string, value: string) => void;
    onRadioChange: (apiParam: string, value: string) => void;
    onRadioClick: (apiParam: string, value: string) => void;
}

/**
 * @since 4.12.0
 */
export function FilterOption({
    group,
    option,
    isChecked,
    onCheckboxChange,
    onRadioChange,
    onRadioClick,
}: FilterOptionProps) {
    const inputId = `${group.id}-${option.value}`;
    const inputType = group.type === 'toggle' ? 'checkbox' : group.type;

    const handleChange = () => {
        if (group.type === 'checkbox' || group.type === 'toggle') {
            onCheckboxChange(group.apiParam, option.value);
        } else {
            onRadioChange(group.apiParam, option.value);
        }
    };

    const handleClick = () => {
        if (group.type === 'radio') {
            onRadioClick(group.apiParam, option.value);
        }
    };

    return (
        <label key={option.value} htmlFor={inputId} className={styles.filterOption}>
            <input
                type={inputType}
                id={inputId}
                name={group.id}
                value={option.value}
                checked={isChecked}
                onChange={handleChange}
                onClick={handleClick}
                className={styles.filterInput}
            />
            {group.type === 'toggle' && <span className={styles.filterToggleSlider} />}
            <span className={styles.filterLabel}>{option.text}</span>
        </label>
    );
}
