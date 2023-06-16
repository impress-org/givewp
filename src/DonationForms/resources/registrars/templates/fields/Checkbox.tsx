import type {CheckboxProps} from '@givewp/forms/propTypes';

/**
 * @since 0.4.0
 */
export default function Checkbox({Label, ErrorMessage, value, fieldError, inputProps}: CheckboxProps) {
    return (
        <label>
            <input type="checkbox" value={value} aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} />
            <Label />

            <ErrorMessage />
        </label>
    );
}
