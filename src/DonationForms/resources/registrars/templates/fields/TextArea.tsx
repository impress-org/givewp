import type {TextareaProps} from '@givewp/forms/propTypes';

export default function TextArea({
    Label,
    ErrorMessage,
    placeholder,
    fieldError,
    inputProps,
    description,
    rows,
}: TextareaProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}
            <textarea
                aria-invalid={fieldError ? 'true' : 'false'}
                {...inputProps}
                placeholder={placeholder}
                rows={rows}
            />
            <ErrorMessage />
        </label>
    );
}
