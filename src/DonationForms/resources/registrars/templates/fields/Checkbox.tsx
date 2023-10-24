import type {CheckboxProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Checkbox({Label, ErrorMessage, value, helpText, fieldError, inputProps}: CheckboxProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <>
            <label>
                <input type="checkbox" value={value} aria-invalid={fieldError ? 'true' : 'false'} {...inputProps} />
                <Label />
            </label>
            {helpText && <FieldDescription description={helpText} />}
            <ErrorMessage />
        </>
    );
}
