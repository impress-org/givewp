import {PhoneProps} from '@givewp/forms/propTypes';
import {useEffect, useState} from 'react';
import IntlTelInput from 'intl-tel-input/react';
import 'intl-tel-input/build/css/intlTelInput.css';
import InputMask from 'react-input-mask';

/**
 * @since 3.9.0
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

    const isIntlTelInput = intlTelInputSettings.initialCountry && intlTelInputSettings.utilsScriptUrl;

    useEffect(() => {
        if (!isIntlTelInput) {
            return;
        }

        // This timeout is necessary to properly load the utilsScript - without this, the autoPlaceholder feature doesn't work.
        const interval = setTimeout(() => {
            window.intlTelInputGlobals.loadUtils(intlTelInputSettings.utilsScriptUrl);
            // It's necessary to fix a missing left padding that can happen in certain cases.
            document.querySelectorAll('.iti__tel-input').forEach(function (input: HTMLInputElement) {
                // @ts-ignore
                const countryContainerWidth = document.querySelector('.iti__country-container').offsetWidth;
                input.style.paddingLeft = String(countryContainerWidth + 4) + 'px';
            });
        }, 100);

        return () => {
            clearInterval(interval);
        };
    }, []);

    const [country, setCountry] = useState<string>(intlTelInputSettings.initialCountry);

    const onChangeNumber = (number: string) => {
        if (number && !window.intlTelInputUtils.isValidNumber(number, country)) {
            const errorCode = window.intlTelInputUtils.getValidationError(number, country);
            setValue(inputProps.name, errorCode);
            setError(inputProps.name, {type: 'custom', message: intlTelInputSettings.errorMap[errorCode]});
        } else {
            setValue(inputProps.name, number);
            trigger(inputProps.name, {shouldFocus: false});
        }
    };

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}

            {isIntlTelInput ? (
                <>
                    <input type={'hidden'} placeholder={placeholder} {...inputProps} />
                    <IntlTelInput
                        onChangeCountry={setCountry}
                        onChangeNumber={onChangeNumber}
                        initOptions={{
                            initialCountry: intlTelInputSettings.initialCountry,
                            showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                            strictMode: intlTelInputSettings.strictMode,
                            i18n: intlTelInputSettings.i18n,
                            useFullscreenPopup: intlTelInputSettings.useFullscreenPopup,
                        }}
                        inputProps={{
                            'aria-invalid': fieldError ? 'true' : 'false',
                        }}
                    />
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
