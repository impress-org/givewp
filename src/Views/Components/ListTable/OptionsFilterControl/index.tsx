import {CheckboxControl, RadioControl} from '@wordpress/components';
import type {FilterOption} from '../filters/types';
import './styles.scss';

/**
 * @since TBD
 */
export interface OptionsFilterControlProps {
    /** Names the group of options for assistive technology. */
    label: string;
    options: FilterOption[];
    /** The chosen option values. */
    value: string[];
    /** Checkboxes when true, radio buttons when false. */
    multiple: boolean;
    onChange: (value: string[]) => void;
}

/**
 * The popover content of an option-set filter: a checkbox list, or radio buttons when only one
 * option can be chosen.
 *
 * @since TBD
 */
export default function OptionsFilterControl({label, options, value, multiple, onChange}: OptionsFilterControlProps) {
    if (!multiple) {
        return (
            <div role="radiogroup" aria-label={label} className="givewp-options-filter-control">
                <RadioControl
                    selected={value[0] ?? ''}
                    options={options}
                    onChange={(selected: string) => onChange([selected])}
                />
            </div>
        );
    }

    const toggle = (optionValue: string, checked: boolean) =>
        onChange(checked ? [...value, optionValue] : value.filter((chosen) => chosen !== optionValue));

    return (
        <div role="group" aria-label={label} className="givewp-options-filter-control">
            {options.map((option) => (
                <CheckboxControl
                    key={option.value}
                    className="givewp-options-filter-control__option"
                    label={option.label}
                    checked={value.includes(option.value)}
                    onChange={(checked: boolean) => toggle(option.value, checked)}
                    __nextHasNoMarginBottom
                />
            ))}
        </div>
    );
}
