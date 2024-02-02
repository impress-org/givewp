import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';

export default function Email({
    Label,
    ErrorMessage,
    description,
    placeholder,
    fieldError,
    inputProps,
}: FieldHasDescriptionProps) {
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
            />

            <ErrorMessage />
        </label>
    );
}
