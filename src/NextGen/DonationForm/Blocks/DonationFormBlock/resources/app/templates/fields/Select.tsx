import type {SelectFieldProps} from '@givewp/forms/propTypes';

export default function Select({Label, ErrorMessage, placeholder, fieldError, options, inputProps}: SelectFieldProps) {
    return (
        <label>
            <Label />

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
