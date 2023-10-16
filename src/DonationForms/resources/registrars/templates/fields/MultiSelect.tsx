import Select from 'react-select';
import {Controller} from 'react-hook-form';

import {MultiSelectProps} from '@givewp/forms/propTypes';
import styles from '../styles.module.scss';

export default function MultiSelect({
    Label,
    ErrorMessage,
    fieldError,
    description,
    fieldType,
    options,
    inputProps,
}: MultiSelectProps) {
    const {useFormContext, useWatch} = window.givewp.form.hooks;
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {name} = inputProps;
    const {control} = useFormContext();
    const fieldValue = useWatch({name: inputProps.name});

    return (
        <fieldset
            className={styles.multiSelectField}
            {...(fieldType === 'checkbox'
                ? {
                      role: 'group',
                      'aria-required': inputProps.required,
                      'aria-invalid': !!fieldError,
                      'aria-describedby': `givewp-field-error-${inputProps.name}`,
                  }
                : {})}
        >
            <legend>
                <Label />
                {description && <FieldDescription description={description} />}
            </legend>
            {fieldType === 'dropdown' ? (
                <Controller
                    name={name}
                    control={control}
                    render={({field: {onChange, value: fieldValue, ref}, fieldState: {invalid}}) => (
                        <Select
                            ref={ref}
                            options={options}
                            defaultValue={
                                fieldValue
                                    ? options.filter(({value: optionValue}) => fieldValue?.includes(optionValue))
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
                    {options.map(({value: optionValue, label}, index) => {
                        const optionId = inputProps.name + '_' + index;
                        return (
                            <div key={index} className="givewp-fields-checkbox__option-container">
                                <input
                                    type="checkbox"
                                    id={optionId}
                                    value={optionValue}
                                    {...inputProps}
                                    checked={fieldValue?.includes(optionValue)}
                                />
                                <label htmlFor={optionId}>{label}</label>
                            </div>
                        );
                    })}
                </div>
            )}

            <ErrorMessage />
        </fieldset>
    );
}
