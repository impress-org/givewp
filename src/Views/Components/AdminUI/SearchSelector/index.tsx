import ReactSelect, {components} from 'react-select';
import styles from './style.module.scss';
import {Controller, useFormContext, useWatch} from 'react-hook-form';
import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';
import SearchMagnifyingGlassIcon from '@givewp/components/AdminUI/Icons/SearchMaginfyingGlassIcon';
import {useState} from 'react';
import {StyleConfig} from '@givewp/components/AdminUI/SearchSelector/styleConfig';

interface SearchSelector {
    options;
    openSelector: boolean;
    setOpenSelector: React.Dispatch<React.SetStateAction<boolean>>;
}

export default function SearchSelector({options, openSelector, setOpenSelector}: SearchSelector) {
    const {control} = useFormContext();
    const [label, setLabel] = useState<null | string>(null);

    const formFieldFormValue = useWatch({
        name: 'form',
    });

    const handleSelectChange = (option, onChange) => {
        onChange(option.value);
        setOpenSelector(!openSelector);
        setLabel(option.label);
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

    const styleConfig = {
        control: (provided, state) => ({
            ...provided,
            border: 'none',
            outline: 'none',
            height: 32,
            background: '#fff',
            borderRadius: 2,
            boxShadow: '0 2px 4px 0 #ebebeb',
        }),
        indicatorsContainer: (provided, state) => ({
            ...provided,
            background: '#fff',
            height: '100%',
            borderRadius: 2,
            padding: '0  .45rem',
        }),
        valueContainer: (provided, state) => ({
            ...provided,
            background: '#fff',
            height: '100%',
            width: '15rem',
            padding: '0  4rem',
            borderRadius: 2,
        }),
    };

    return (
        <div className={styles.formSelectorContainer}>
            <ToggleOptionLabel label={label ?? formFieldFormValue?.name} />
            <div>
                {openSelector && (
                    <Controller
                        control={control}
                        name="form"
                        render={({
                            field: {onChange, onBlur, value, name, ref},
                            fieldState: {invalid, isTouched, isDirty, error},
                            formState,
                        }) => (
                            <ReactSelect
                                name={name}
                                options={options}
                                onChange={(option) => handleSelectChange(option, onChange)}
                                isClearable={false}
                                isSearchable={true}
                                placeholder={''}
                                onBlur={onBlur}
                                components={{
                                    IndicatorSeparator: () => null,
                                    DropdownIndicator,
                                }}
                                styles={StyleConfig}
                            />
                        )}
                    />
                )}
            </div>
        </div>
    );
}
