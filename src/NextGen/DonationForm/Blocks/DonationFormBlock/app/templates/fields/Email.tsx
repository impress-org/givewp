import type {FieldProps} from '@givewp/forms/propTypes';

export default function Email({label, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            <span>{label}</span>
            <input type="email" aria-invalid={fieldError ? "true" : "false"} {...inputProps} />

            <div className="error-message">
                {fieldError && <p role="alert">{fieldError}</p>}
            </div>
        </label>
    );
}
