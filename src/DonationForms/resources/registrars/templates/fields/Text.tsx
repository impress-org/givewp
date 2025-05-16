import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import autoCompleteAttr from '@givewp/forms/registrars/templates/fields/utils/autoCompleteAttr';

/**
 * @unreleased Add autoComplete support
 */
export default function Text({
    Label,
    ErrorMessage,
    fieldError,
    description,
    placeholder,
    inputProps,
}: FieldHasDescriptionProps) {
    const autoComplete = autoCompleteAttr(inputProps?.name);
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const inputId = `givewp-fields-text-${inputProps.name}-input`;

    return (
        <label className={fieldError && 'givewp-field-error-label'} htmlFor={inputId}>
            <Label />
            {description && <FieldDescription description={description} />}
            <input
                id={inputId}
                type="text"
                aria-invalid={fieldError ? 'true' : 'false'}
                placeholder={placeholder}
                {...inputProps}
                autoComplete={autoComplete}
            />

            <ErrorMessage />
        </label>
    );
}
