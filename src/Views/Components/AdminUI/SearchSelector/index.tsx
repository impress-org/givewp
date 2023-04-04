import React, {useState} from 'react';
import {Controller, useFormContext, useWatch} from 'react-hook-form';
import ReactSelect, {components} from 'react-select';

import SearchMagnifyingGlassIcon from '@givewp/components/AdminUI/Icons/SearchMaginfyingGlassIcon';
import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';

import styles from './style.module.scss';
import {StyleConfig} from './StyleConfig';

/**
 *
 * @unreleased
 */

export type SearchSelectorProps = {
    options: Array<{
        value: number;
        label: string;
    }>;
    defaultLabel?: string;
    name: string;
    placeholder: string;
};

export default function SearchSelector({options, name, placeholder}: SearchSelectorProps) {
    const {control} = useFormContext();
    const [focus, setFocus] = useState(false);

    const form = useWatch({
        name: 'formId',
    });

    const defaultValue = options?.find((option) => option['value'] === form);

    const DropdownIndicator = (props) => {
        return (
            <components.DropdownIndicator {...props}>
                {focus ? <SearchMagnifyingGlassIcon /> : <DownArrowIcon color={'#0e0e0e'} />}
            </components.DropdownIndicator>
        );
    };

    const handleFocus = () => {
        setFocus(!focus);
    };

    return (
        <div className={styles.formSelectorContainer}>
            <Controller
                control={control}
                name={name}
                render={({
                    field: {onChange, onBlur, value, name, ref},
                    fieldState: {invalid, isTouched, isDirty, error},
                    formState,
                }) => (
                    <ReactSelect
                        ref={ref}
                        name={name}
                        value={options.find((option) => option.value === value)}
                        options={options}
                        onChange={(selectedOption) => {
                            onChange(selectedOption.value);
                            handleFocus();
                        }}
                        onFocus={handleFocus}
                        onBlur={handleFocus}
                        isClearable={false}
                        isSearchable={true}
                        menuPlacement={'bottom'}
                        placeholder={placeholder}
                        components={{
                            IndicatorSeparator: () => null,
                            DropdownIndicator,
                        }}
                        styles={StyleConfig}
                    />
                )}
            />
        </div>
    );
}
