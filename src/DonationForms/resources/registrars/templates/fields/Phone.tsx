import {PhoneProps} from '@givewp/forms/propTypes';
import {useEffect, useState} from 'react';
import IntlTelInput from 'intl-tel-input/react';
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

    const isIntlTelInput = intlTelInputSettings.initialCountry && intlTelInputSettings.utilsScriptUrl;
    const isFormBuilderPreview = window.location.href.indexOf('about:srcdoc') > -1;

    const intlInitOptions = {
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
        intlInitOptions['useFullscreenPopup'] = false;
    }

    useEffect(() => {
        if (!isIntlTelInput) {
            return;
        }

        const interval = setTimeout(
            () => {
                window.intlTelInputGlobals.loadUtils(intlTelInputSettings.utilsScriptUrl);

                // It's necessary to fix a missing padding in the form builder preview.
                if (isFormBuilderPreview) {
                    document.querySelectorAll('.iti__tel-input').forEach(function (input: HTMLInputElement) {
                        // @ts-ignore
                        const countryContainerWidth = document.querySelector('.iti__country-container').offsetWidth;
                        input.style.paddingLeft = String(countryContainerWidth + 4) + 'px';
                    });
                }
            },
            isFormBuilderPreview ? 100 : 0 // It's necessary to properly load the utilsScript in the form builder preview - without this, the autoPlaceholder feature doesn't work.
        );

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
                        initOptions={intlInitOptions}
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
