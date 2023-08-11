import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';

export default function File({
    Label,
    ErrorMessage,
    fieldError,
    placeholder,
    description,
    inputProps,
}: FieldHasDescriptionProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {setValue} = window.givewp.form.hooks.useFormContext();
    const {name} = inputProps;

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}

            <input
                type="file"
                aria-invalid={fieldError ? 'true' : 'false'}
                // TODO: Update with accept prop
                //accept="image/png, image/jpeg"
                placeholder={placeholder}
                onChange={(e) => {
                    setValue(name, e.target.files[0]);
                }}
            />

            <input type="hidden" {...inputProps} />

            <ErrorMessage />
        </label>
    );
}
