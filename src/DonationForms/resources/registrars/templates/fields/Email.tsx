import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import autoCompleteMapping from '@givewp/forms/registrars/templates/fields/utils/autoCompleteMapping';

/**
 * @unreleased Add autoComplete support
 */
export default function Email({
    Label,
    ErrorMessage,
    description,
    placeholder,
    fieldError,
    inputProps,
}: FieldHasDescriptionProps) {
    const autoComplete = autoCompleteMapping[inputProps?.name] || '';
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
                {...(autoComplete && {autoComplete: autoComplete})}
            />

            <ErrorMessage />
        </label>
    );
}
