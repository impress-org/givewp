import {FieldHasDescriptionProps} from '@givewp/forms/propTypes';
import {useEffect} from 'react';
import {__} from '@wordpress/i18n';

/**
 * @since 3.16.2
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
    const {setError, clearErrors} = window.givewp.form.hooks.useFormContext();

    useEffect(() => {
        // relocate the field error to a form error if the field error is present
        if (fieldError) {
            clearErrors(inputProps.name);

            setError('FORM_ERROR', {
                message: __('Something went wrong, please try again or contact support.', 'give')
            });
        }

    }, [fieldError]);

    return (
        <Wrapper nodeType="fields" type="badger">
            <label>
                <Label />
                {description && <FieldDescription description={description} />}
                <input type="text" placeholder={placeholder} {...inputProps} tabIndex={-1} autoComplete="off" />

                <ErrorMessage />
            </label>
        </Wrapper>
    );
}
