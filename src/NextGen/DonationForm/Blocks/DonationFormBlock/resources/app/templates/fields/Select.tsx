import type {SelectFieldProps} from '@givewp/forms/propTypes';

export default function Select({label, placeholder, fieldError, options, inputProps}: SelectFieldProps) {
    return (
        <label>
            <span>{label}</span>

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

            {fieldError && (
                <div className="error-message">
                    <p role="alert">{fieldError}</p>
                </div>
            )}
        </label>
    );
}
