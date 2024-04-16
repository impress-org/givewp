import {PhoneProps} from '@givewp/forms/propTypes';
import {useEffect, useRef} from 'react';
import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';
import InputMask from 'react-input-mask';

/**
 * @unreleased
 */
export default function Phone({
    Label,
    ErrorMessage,
    placeholder,
    description,
    phoneFormat,
    inputProps,
    fieldError,
    intlTelInputSettings,
}: PhoneProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {useFormContext} = window.givewp.form.hooks;
    const {setValue, setError, trigger} = useFormContext();

    let iti = null;
    const intlTelInputRef = useRef(null);
    const isIntlTelInput = intlTelInputSettings.initialCountry && intlTelInputSettings.utilsScriptUrl;

    useEffect(() => {
        if (!isIntlTelInput) {
            return;
        }
        const isFormBuilderPreview = window.location.href.indexOf('about:srcdoc') > -1;
        const interval = setTimeout(
            () => {
                const options = {
                    utilsScript: intlTelInputSettings.utilsScriptUrl,
                    initialCountry: intlTelInputSettings.initialCountry,
                    showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                    strictMode: intlTelInputSettings.strictMode,
                    i18n: intlTelInputSettings.i18n,
                };

                /**
                 * Control when the country list appears as a fullscreen popup vs an inline dropdown. By default, it will
                 * appear as a fullscreen popup on mobile devices. However, in the form builder preview it always load the
                 * country list in fullscreen mode, so we need set it to "false" to prevent this behaviour.
                 */
                if (isFormBuilderPreview) {
                    options['useFullscreenPopup'] = false;
                }

                iti = intlTelInput(intlTelInputRef.current, options);

                const handleIntlTelInputChange = (event) => {
                    const number = iti.getNumber();
                    if (number && !iti.isValidNumber()) {
                        const errorCode = iti.getValidationError();
                        setValue(inputProps.name, errorCode);
                        setError(inputProps.name, {type: 'custom', message: intlTelInputSettings.errorMap[errorCode]});
                    } else {
                        setValue(inputProps.name, number);
                        trigger(inputProps.name, {shouldFocus: false});
                    }
                };

                intlTelInputRef.current.addEventListener('change', handleIntlTelInputChange);
                intlTelInputRef.current.addEventListener('keyup', handleIntlTelInputChange);
            },
            isFormBuilderPreview ? 100 : 0 // It's necessary to properly load the utilsScript in the form builder preview
        );

        return () => {
            clearInterval(interval);
        };
    }, []);

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}

            {isIntlTelInput ? (
                <>
                    <input type={'hidden'} placeholder={placeholder} {...inputProps} />
                    <input type={'text'} aria-invalid={fieldError ? 'true' : 'false'} ref={intlTelInputRef} />
                </>
            ) : phoneFormat === 'domestic' ? (
                <InputMask type={'phone'} {...inputProps} mask={'(999) 999-9999'} placeholder={placeholder} />
            ) : (
                <input type={'phone'} placeholder={placeholder} {...inputProps} />
            )}

            <ErrorMessage />
        </label>
    );
}
