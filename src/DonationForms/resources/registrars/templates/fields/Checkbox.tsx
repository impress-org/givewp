import type {CheckboxProps} from '@givewp/forms/propTypes';

/**
 * @since 4.0.0 updated inputProps and value to prevent checkbox from being a controlled component
 * @since 3.0.0
 */
export default function Checkbox({Label, ErrorMessage, value, helpText, fieldError, inputProps}: CheckboxProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    const {checked, ...rest} = inputProps;
    const inputValue = value === null ? undefined : value;

    return (
        <>
            <label>
                <input type="checkbox" value={inputValue} defaultChecked={checked} aria-invalid={fieldError ? 'true' : 'false'} {...rest} />
                <Label />
            </label>
            {helpText && <FieldDescription description={helpText} />}
            <ErrorMessage />
        </>
    );
}
