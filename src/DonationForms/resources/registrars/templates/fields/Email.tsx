import {useEffect, useState} from 'react';
import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import autoCompleteAttr from '@givewp/forms/registrars/templates/fields/utils/autoCompleteAttr';
import {DONOR_AUTHENTICATED_EVENT} from '@givewp/forms/app/utilities/donorAuthenticatedEvent';

/**
 * @since TBD Lock the field when the inline login signs the donor in.
 * @since 4.3.0 Add autoComplete support
 */
export default function Email({
    Label,
    ErrorMessage,
    description,
    placeholder,
    fieldError,
    inputProps,
}: FieldHasDescriptionProps) {
    const autoComplete = autoCompleteAttr(inputProps?.name);
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const [isLockedByLogin, setIsLockedByLogin] = useState(false);
    const isReadOnly = Boolean((inputProps as {readOnly?: boolean})?.readOnly);

    useEffect(() => {
        const lock = () => setIsLockedByLogin(true);

        document.addEventListener(DONOR_AUTHENTICATED_EVENT, lock);

        return () => document.removeEventListener(DONOR_AUTHENTICATED_EVENT, lock);
    }, []);

    return (
        <label className={fieldError && 'givewp-field-error-label'}>
            <Label />
            {description && <FieldDescription description={description} />}
            <input
                type="email"
                aria-invalid={fieldError ? 'true' : 'false'}
                placeholder={placeholder}
                {...inputProps}
                readOnly={isReadOnly || isLockedByLogin || undefined}
                disabled={isReadOnly || isLockedByLogin || undefined}
                autoComplete={autoComplete}
            />

            <ErrorMessage />
        </label>
    );
}
