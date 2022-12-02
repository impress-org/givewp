import type {FieldProps} from '@givewp/forms/propTypes';

export default function Email({Label, ErrorMessage, placeholder, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            <Label />
            <input
                type="email"
                aria-invalid={fieldError ? 'true' : 'false'}
                placeholder={placeholder}
                {...inputProps}
            />

            <ErrorMessage />
        </label>
    );
}
