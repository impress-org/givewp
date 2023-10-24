import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';

export default function Url({
    Label,
    ErrorMessage,
    fieldError,
    placeholder,
    description,
    inputProps,
}: FieldHasDescriptionProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}
            <input type="text" aria-invalid={fieldError ? 'true' : 'false'} placeholder={placeholder} {...inputProps} />

            <ErrorMessage />
        </label>
    );
}
