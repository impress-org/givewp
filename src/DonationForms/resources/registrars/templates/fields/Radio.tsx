import {SelectableFieldProps} from '@givewp/forms/propTypes';

export default function Radio({
    Label,
    ErrorMessage,
    options,
    description,
    inputProps,
    fieldError,
}: SelectableFieldProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        options.length > 0 && (
            <fieldset
                role="radiogroup"
                aria-required={inputProps.required}
                aria-invalid={!!fieldError}
                aria-describedby={`givewp-field-error-${inputProps.name}`}
            >
                <legend>
                    <Label />
                    {description && <FieldDescription description={description} />}
                </legend>
                <div className="givewp-fields-radio__options">
                    {options.map(({value, label}, index) => {
                        const optionId = inputProps.name + '_' + index;
                        return (
                            <div key={index} className="givewp-fields-radio__option-container">
                                <input
                                    type="radio"
                                    id={optionId}
                                    name={inputProps.name}
                                    value={value}
                                    {...inputProps}
                                />
                                <label htmlFor={optionId}>{label}</label>
                            </div>
                        );
                    })}
                </div>

                <ErrorMessage />
            </fieldset>
        )
    );
}
