import ReactSelect, {components} from 'react-select';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
import {Controller, useFormContext, useWatch} from 'react-hook-form';
import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';
import SearchMagnifyingGlassIcon from '@givewp/components/AdminUI/Icons/SearchMaginfyingGlassIcon';
import {useState} from 'react';
import {StyleConfig} from './StyleConfig';

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
                )}
            </div>
        </div>
    );
}
