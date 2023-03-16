import {useState} from 'react';
import {Controller, useFormContext, useWatch} from 'react-hook-form';
import ReactSelect, {components} from 'react-select';
import {__} from '@wordpress/i18n';

import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';
import SearchMagnifyingGlassIcon from '@givewp/components/AdminUI/Icons/SearchMaginfyingGlassIcon';

import styles from './style.module.scss';
import {StyleConfig} from './StyleConfig';

import {SearchSelector} from '@givewp/components/AdminUI/SearchSelector/types';

/**
 *
 * @unreleased
 */
export default function SearchSelector({options, openSelector, setOpenSelector, defaultLabel}: SearchSelector) {
    const {control} = useFormContext();
    const [label, setLabel] = useState<null | string>(defaultLabel);

    const formFieldFormValue = useWatch({
        name: 'form',
    });

    const handleSelectChange = (selectedOption) => {
        setOpenSelector(!openSelector);
        setLabel(selectedOption.label);
    };

    const ToggleOptionLabel = ({label}) => (
        <button type="button" className={styles.searchSelect} onClick={() => setOpenSelector(!openSelector)}>
            <span>{label}</span>
            <DownArrowIcon />
        </button>
    );

    const DropdownIndicator = (props) => {
        return (
            <components.DropdownIndicator {...props}>
                <SearchMagnifyingGlassIcon />
            </components.DropdownIndicator>
        );
    };

    return (
        <div className={styles.formSelectorContainer}>
            <ToggleOptionLabel label={label ?? formFieldFormValue?.name} />
            <div className={openSelector ? styles.reveal : styles.hidden}>
                <Controller
                    control={control}
                    name="form"
                    render={({
                        field: {onChange, onBlur, value, name, ref},
                        fieldState: {invalid, isTouched, isDirty, error},
                        formState,
                    }) => (
                        <ReactSelect
                            ref={ref}
                            name={name}
                            menuIsOpen={openSelector}
                            value={options.find((option) => option.value === value)}
                            options={options}
                            onChange={(selectedOption) => {
                                handleSelectChange(selectedOption);
                                onChange(selectedOption.value);
                            }}
                            isClearable={false}
                            isSearchable={true}
                            placeholder={__('Search for a donation form', 'give')}
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
        </div>
    );
}
