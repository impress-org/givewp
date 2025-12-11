import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import autoCompleteAttr from '@givewp/forms/registrars/templates/fields/utils/autoCompleteAttr';

/**
 * @since 4.3.0 Add autoComplete support
 */
export default function Email({
    Label,
    ErrorMessage,
    description,
    placeholder,
    fieldError,
    inputProps,
}: FieldHasDescriptionProps) {
    const autoComplete = autoCompleteAttr(inputProps?.name);
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <label className={fieldError && 'givewp-field-error-label'}>
            <Label />
            {description && <FieldDescription description={description} />}
            <input
                type="email"
                aria-invalid={fieldError ? 'true' : 'false'}
                placeholder={placeholder}
                {...inputProps}
                autoComplete={autoComplete}
            />

            <ErrorMessage />
        </label>
    );
}
