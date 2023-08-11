import type {SelectableFieldProps} from '@givewp/forms/propTypes';

export default function Select({
    Label,
    ErrorMessage,
    placeholder,
    fieldError,
    options,
    description,
    inputProps,
}: SelectableFieldProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}
            <select {...inputProps} aria-invalid={fieldError ? 'true' : 'false'}>
                {placeholder && (
                    <>
                        <option hidden>{placeholder}</option>
                        <option disabled>{placeholder}</option>
                    </>
                )}
                {options.map(({value, label}) => (
                    <option key={value} value={value}>
                        {label ?? value}
                    </option>
                ))}
            </select>

            <ErrorMessage />
        </label>
    );
}
