import type {TextareaProps} from '@givewp/forms/propTypes';

export default function TextArea({Label, ErrorMessage, placeholder, fieldError, inputProps, helpText}: TextareaProps) {
    return (
        <label>
            <Label />
            {helpText && (
                <div className="givewp-fields-textarea__description">
                    <small>{helpText}</small>
                </div>
            )}
            <textarea aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} placeholder={placeholder} />
            <ErrorMessage />
        </label>
    );
}
