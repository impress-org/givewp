import InputMask from 'react-input-mask';

import {PhoneProps} from '@givewp/forms/propTypes';
import {useEffect} from 'react';

export default function Phone({
    Label,
    ErrorMessage,
    placeholder,
    description,
    phoneFormat,
    inputProps,
    name,
    intlTelInputSettings,
    intlTelInputFullNumber,
}: PhoneProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;
    const {useWatch, useFormContext, useFormState} = window.givewp.form.hooks;
    const {setValue, clearErrors} = useFormContext();

    const intlTelInputId = name + '_intl_tel_input';

    useEffect(() => {
        //console.log('getWindowData: ', getWindowData());

        if (phoneFormat) {
            return;
        }

        const input = document.querySelector('#' + intlTelInputId);
        console.log('input: ', input);

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
                hiddenInput: function (telInputName) {
                    return {
                        phone: name + '--international-format',
                        country: name + '--country-code',
                    };
                },
                showSelectedDialCode: true,
                strictMode: true,
                utilsScript: intlTelInputSettings.utilsScriptUrl,
                initialCountry: intlTelInputSettings.initialCountry,
                i18n: JSON.parse(intlTelInputSettings.i18n),
            });

            const handleIntlTelInputChange = (event) => {
                console.log('value: ', event.target.value);
                console.log('intl.getNumber(): ', intl.getNumber());
                setValue(inputProps.name, intl.getNumber());
            };

            input.addEventListener('change', handleIntlTelInputChange);
            input.addEventListener('keyup', handleIntlTelInputChange);
        };

        document.body.appendChild(script);
    }, []);

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}
            {phoneFormat === 'domestic' ? (
                <InputMask type={'phone'} {...inputProps} mask={'(999) 999-9999'} placeholder={placeholder} />
            ) : (
                <>
                    <input id={intlTelInputId} type={'text'} />
                    <input type={'hidden'} placeholder={placeholder} {...inputProps} />
                </>
            )}

            <ErrorMessage />
        </label>
    );
}
