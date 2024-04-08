import {PhoneProps} from '@givewp/forms/propTypes';
import {useEffect} from 'react';
import InputMask from 'react-input-mask';

export default function Phone({
    Label,
    ErrorMessage,
    placeholder,
    description,
    phoneFormat,
    inputProps,
    intlTelInputSettings,
}: PhoneProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();

    const intlTelInputId = inputProps.name + '_intl_tel_input';
    const isIntlTelInput =
        intlTelInputSettings.cssUrl && intlTelInputSettings.scriptUrl && intlTelInputSettings.utilsScriptUrl;

    useEffect(() => {
        if (!isIntlTelInput) {
            return;
        }

        const input: HTMLInputElement = document.querySelector('#' + intlTelInputId);

        const css = document.createElement('link');
        css.href = intlTelInputSettings.cssUrl;
        css.rel = 'stylesheet';
        document.body.appendChild(css);

        const script = document.createElement('script');
        script.src = intlTelInputSettings.scriptUrl;
        script.async = true;
        script.onload = () => {
            // @ts-ignore
            const intl = window.intlTelInput(input, {
                showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                strictMode: intlTelInputSettings.strictMode,
                utilsScript: intlTelInputSettings.utilsScriptUrl,
                initialCountry: intlTelInputSettings.initialCountry,
                i18n: intlTelInputSettings.i18n,
            });

            const handleIntlTelInputChange = (event) => {
                const number = intl.getNumber();
                console.log('number: ', number);
                if (number && !intl.isValidNumber()) {
                    const errorCode = intl.getValidationError();
                    setValue(inputProps.name, errorCode);
                    console.log('error: ', errorCode);
                } else {
                    setValue(inputProps.name, number);
                }
            };

            input.style.paddingLeft = '87px'; // It's necessary to fix a missing padding in the form preview

            input.addEventListener('change', handleIntlTelInputChange);
            input.addEventListener('keyup', handleIntlTelInputChange);
        };
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(css);
            document.body.removeChild(script);
        };
    }, []);

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}

            {isIntlTelInput ? (
                <>
                    <input type={'hidden'} placeholder={placeholder} {...inputProps} />
                    <input id={intlTelInputId} type={'text'} />
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
