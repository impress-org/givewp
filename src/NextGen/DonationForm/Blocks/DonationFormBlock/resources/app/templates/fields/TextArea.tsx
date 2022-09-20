import type {FieldProps} from '@givewp/forms/propTypes';

export default function TextArea({label, placeholder, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            {label}
            <textarea {...inputProps} placeholder={placeholder}/>
            {fieldError && (
                <div className="error-message">
                    <p role="alert">{fieldError}</p>
                </div>
            )}
        </label>
    );
}
