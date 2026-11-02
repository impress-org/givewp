import {useEffect} from 'react';
import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import autoCompleteAttr from '@givewp/forms/registrars/templates/fields/utils/autoCompleteAttr';

/**
 * @since 4.19.1.1 Lock the field when the donor is logged in, including after the inline login.
 * @since 4.3.0 Add autoComplete support
 */
export default function Email({
    Label,
    ErrorMessage,
    description,
    placeholder,
    fieldError,
    inputProps,
    readOnly,
}: FieldHasDescriptionProps) {
    const autoComplete = autoCompleteAttr(inputProps?.name);
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {useDonationFormState, useFormContext} = window.givewp.form.hooks;
    const {isDonorAuthenticated, donorEmail} = useDonationFormState();
    const {getValues, setValue} = useFormContext();
    const isLocked = readOnly || isDonorAuthenticated;

    /*
     * On a multi-step form the login block can be on a later step than this field. Earlier steps
     * stay mounted with their own values, so without this a donor who goes back would see, and
     * carry forward, the email typed before logging in.
     */
    useEffect(() => {
        if (isDonorAuthenticated && donorEmail && getValues(inputProps.name) !== donorEmail) {
            setValue(inputProps.name, donorEmail);
        }
    }, [isDonorAuthenticated, donorEmail]);

    return (
        <label className={fieldError && 'givewp-field-error-label'}>
            <Label />
            {description && <FieldDescription description={description} />}
            <input
                type="email"
                aria-invalid={fieldError ? 'true' : 'false'}
                placeholder={placeholder}
                {...inputProps}
                readOnly={isLocked || undefined}
                disabled={isLocked || undefined}
                autoComplete={autoComplete}
            />

            <ErrorMessage />
        </label>
    );
}
