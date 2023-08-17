import Select from 'react-select';
import {Controller} from 'react-hook-form';

import {MultiSelectProps} from '@givewp/forms/propTypes';
import styles from '../styles.module.scss';
import {useDonationFormState} from "@givewp/forms/app/store";

export default function MultiSelect({
    Label,
    ErrorMessage,
    fieldError,
    defaultValue,
    description,
    fieldType,
    options,
    inputProps,
}: MultiSelectProps) {
    const {useFormContext} = window.givewp.form.hooks;
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {name} = inputProps;
    const {control} = useFormContext();

    return (
        <fieldset className={styles.multiSelectField}>
            <label>
                <Label />
                {description && <FieldDescription description={description} />}
            </label>
            {fieldType === 'dropdown' ? (
                <Controller
                    name={name}
                    control={control}
                    render={({
                        field: {onChange, value, ref},
                        fieldState: {invalid},
                    }) => (
                        <Select
                            ref={ref}
                            options={options}
                            defaultValue={
                                value
                                    ? options.filter(({value: optionValue}) =>
                                          Object.values(value)?.includes(optionValue)
                                      )
                                    : null
                            }
                            isMulti={true}
                            isClearable={true}
                            isSearchable={false}
                            className="givewp-fields-multiSelect__input"
                            classNamePrefix="givewp-fields-multiSelect"
                            onChange={(newValue) => onChange(newValue.map(({value}) => value))}
                            aria-invalid={invalid ? 'true' : 'false'}
                        />
                    )}
                />
            ) : (
                <div className="givewp-fields-checkbox__options">
                    {options.map(({value, label}, index) => (
                        <div key={index} className="givewp-fields-checkbox__option--container">
                            <input type="checkbox" value={value} {...inputProps} />
                            <label htmlFor={inputProps.name}>{label}</label>
                        </div>
                    ))}
                </div>
            )}

            <ErrorMessage />
        </fieldset>
    );
}
