import type {FieldProps} from '@givewp/forms/propTypes';

export default function Text({label, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            <span>{label}</span>
            <input type="text" aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} />

            {fieldError && (
                <div className="error-message">
                    <p role="alert">{fieldError}</p>
                </div>
            )}
        </label>
    );
}
