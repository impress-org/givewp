import type {FieldProps} from '@givewp/forms/propTypes';

export default function Text({label, placeholder, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            <span>{label}</span>
            <input type="text" aria-invalid={fieldError ? 'true' : 'false'} placeholder={placeholder} {...inputProps} />

            {fieldError && (
                <div className="error-message">
                    <p role="alert">{fieldError}</p>
                </div>
            )}
        </label>
    );
}
