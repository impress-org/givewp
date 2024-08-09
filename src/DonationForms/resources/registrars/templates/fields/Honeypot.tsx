import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
 */
export default function Honeypot({
                                     Label,
                                     ErrorMessage,
                                     fieldError,
                                     description,
                                     placeholder,
                                     inputProps
                                 }: FieldHasDescriptionProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const Wrapper = window.givewp.form.templates.layouts.wrapper;

    return (
        <Wrapper nodeType="fields" type="badger">
            <label className={fieldError && 'givewp-field-error-label'}>
                <Label />
                {description && <FieldDescription description={description} />}
                <input type="text" aria-invalid={fieldError ? 'true' : 'false'}
                       placeholder={placeholder} {...inputProps} tabIndex={-1} autoComplete="off" />

                <ErrorMessage />
            </label>
        </Wrapper>
    );
}
