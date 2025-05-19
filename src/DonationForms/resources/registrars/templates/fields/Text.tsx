import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import autoCompleteAttr from '@givewp/forms/registrars/templates/fields/utils/autoCompleteAttr';

/**
 * @since 4.3.0 Add autoComplete support and aria-labelledby and aria-describedby attributes.
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
    const baseId = `givewp-fields-text-${inputProps.name}`;
    const inputId = `${baseId}-input`;
    const labelId = `${baseId}-label`;
    const descriptionId = `${baseId}-description`;

    return (
        <label
            className={fieldError && 'givewp-field-error-label'}
            htmlFor={inputId}
        >
            <span id={labelId}>
                <Label />
            </span>
            {description && (
                <span id={descriptionId}>
                    <FieldDescription description={description} />
                </span>
            )}

            <input
                id={inputId}
                type="text"
                aria-invalid={fieldError ? 'true' : 'false'}
                aria-labelledby={labelId}
                aria-describedby={description ? descriptionId : undefined}
                placeholder={placeholder}
                {...inputProps}
                autoComplete={autoComplete}
            />

            <ErrorMessage />
        </label>
    );
}
