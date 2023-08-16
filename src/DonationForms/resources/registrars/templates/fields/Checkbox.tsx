import type {CheckboxProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Checkbox({Label, ErrorMessage, value, helpText, fieldError, inputProps}: CheckboxProps) {
    return (
        <label>
            <input type="checkbox" value={value} aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} />
            <Label />

            {helpText && (
                <div className="givewp-fields-checkbox__description">
                    <small>{helpText}</small>
                </div>
            )}

            <ErrorMessage />
        </label>
    );
}
