import {FileProps} from '@givewp/forms/propTypes';

/**
 * @since 4.3.0 add aria-required attribute.
 */
export default function File({Label, allowedMimeTypes, ErrorMessage, fieldError, description, inputProps}: FileProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {setValue} = window.givewp.form.hooks.useFormContext();
    const {name} = inputProps;

    return (
        <>
            <label htmlFor={`${name}-field`}>
                <Label />
            </label>
            {description && <FieldDescription description={description} />}

            <input
                id={`${name}-field`}
                type="file"
                aria-invalid={fieldError ? 'true' : 'false'}
                accept={allowedMimeTypes.join(',')}
                onChange={(e) => {
                    setValue(name, e.target.files[0]);
                }}
                aria-required={inputProps['aria-required']}
            />

            <input type="hidden" {...inputProps} />

            <ErrorMessage />
        </>
    );
}
