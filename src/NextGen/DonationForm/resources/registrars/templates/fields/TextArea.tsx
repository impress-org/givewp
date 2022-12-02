import type {FieldProps} from '@givewp/forms/propTypes';

export default function TextArea({Label, ErrorMessage, placeholder, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            <Label />
            <textarea aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} placeholder={placeholder} />
            <ErrorMessage />
        </label>
    );
}
