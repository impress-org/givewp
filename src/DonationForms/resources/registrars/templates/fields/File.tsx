import type {FieldProps} from '@givewp/forms/propTypes';

export default function File({Label, ErrorMessage, fieldError, placeholder, inputProps}: FieldProps) {
    const {setValue} = window.givewp.form.hooks.useFormContext();
    const {name} = inputProps;

    return (
        <label>
            <Label />

            <input
                type="file"
                aria-invalid={fieldError ? 'true' : 'false'}
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
