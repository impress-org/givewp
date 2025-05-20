import {FileProps} from '@givewp/forms/propTypes';
import {__, sprintf} from '@wordpress/i18n';

/**
 * @since 4.3.0 Add aria-required attribute and file size and type validations.
 */
export default function File({Label, allowedMimeTypes, maxUploadSize, ErrorMessage, fieldError, description, inputProps}: FileProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {setValue, setError} = window.givewp.form.hooks.useFormContext();
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
                    const file = e.target.files[0];

                    if (!file) {
                        return;
                    }

                    if (!allowedMimeTypes.includes(file.type)) {
                        setError(name, {message: __('The selected file must be a valid file type.', 'give')});
                        return;
                    }

                    if (file.size > maxUploadSize) {
                        setError(name, {message: sprintf(__('The selected file must be less than or equal to %d bytes.', 'give'), maxUploadSize)});
                        return;
                    }

                    setError(name, undefined);
                    setValue(name, file);
                }}
                aria-required={inputProps['aria-required']}
            />

            <input type="hidden" {...inputProps} />

            <ErrorMessage />
        </>
    );
}
