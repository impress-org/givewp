import React, {useState} from 'react';
import {Controller, useFormContext, useWatch} from 'react-hook-form';
import ReactSelect, {components} from 'react-select';
import SearchMagnifyingGlassIcon from '@givewp/components/AdminUI/Icons/SearchMaginfyingGlassIcon';

import styles from './style.module.scss';
import {StyleConfig} from './StyleConfig';

import {SearchSelector} from '@givewp/components/AdminUI/SearchSelector/types';
import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';

/**
 *
 * @unreleased
 */
export default function SearchSelector({options, name, placeholder}: SearchSelector) {
    const {control} = useFormContext();
    const [focus, setFocus] = useState(false);

    const form = useWatch({
        name: 'form',
    });

    const defaultValue = options?.find((option) => option['value'] === form);

    const DropdownIndicator = (props) => {
        return (
            <components.DropdownIndicator {...props}>
                {focus ? <SearchMagnifyingGlassIcon /> : <DownArrowIcon />}
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
