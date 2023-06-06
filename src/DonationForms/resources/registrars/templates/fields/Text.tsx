import type {FieldProps} from '@givewp/forms/propTypes';

export default function Text({Label, ErrorMessage, fieldError, placeholder, inputProps}: FieldProps) {
    return (
        <label>
            <Label />
            <input type="text" aria-invalid={fieldError ? 'true' : 'false'} placeholder={placeholder} {...inputProps} />

            <ErrorMessage />
        </label>
    );
}
