import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';

export default function Text({
    Label,
    ErrorMessage,
    fieldError,
    description,
    placeholder,
    inputProps,
}: FieldHasDescriptionProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <label className={fieldError && 'givewp-field-error-label'}>
            <Label />
            {description && <FieldDescription description={description} />}
            <input type="text" aria-invalid={fieldError ? 'true' : 'false'} placeholder={placeholder} {...inputProps} />

            <ErrorMessage />
        </label>
    );
}
