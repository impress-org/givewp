import type {FieldProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
 */
export default function Checkbox({Label, ErrorMessage, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            <input type="checkbox" aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} />
            <Label />

            <ErrorMessage />
        </label>
    );
}
