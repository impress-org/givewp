import type {FieldProps} from '@givewp/forms/propTypes';

export default function TextArea({label, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            {label}
            <textarea {...inputProps} />
            {fieldError && (
                <div className="error-message">
                    <p role="alert">{fieldError}</p>
                </div>
            )}
        </label>
    );
}
