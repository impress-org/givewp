import {Controller, useFormContext} from 'react-hook-form';
import ReactSelect, {components} from 'react-select';
import SearchMagnifyingGlassIcon from '@givewp/components/AdminUI/Icons/SearchMaginfyingGlassIcon';

import styles from './style.module.scss';
import {StyleConfig} from './StyleConfig';

import {SearchSelector} from '@givewp/components/AdminUI/SearchSelector/types';

/**
 *
 * @unreleased
 */
export default function SearchSelector({options, name, placeholder}: SearchSelector) {
    const {control} = useFormContext();

    const DropdownIndicator = (props) => {
        return (
            <components.DropdownIndicator {...props}>
                <SearchMagnifyingGlassIcon />
            </components.DropdownIndicator>
        );
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
                        isClearable={false}
                        isSearchable={true}
                        menuPlacement={'bottom'}
                        placeholder={placeholder}
                        onBlur={onBlur}
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
