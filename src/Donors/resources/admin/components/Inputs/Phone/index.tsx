/**
 * External Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';
import IntlTelInput from 'intl-tel-input/react';
import 'intl-tel-input/build/css/intlTelInput.css';

/**
 * Internal Dependencies
 */
import styles from './styles.module.scss';
import { IntlTelInputSettings } from '../../types';

/**
 * @since 4.4.0
 */
type PhoneInputProps = {
    id?: string;
    value?: string;
    onChange: (value: string) => void;
    onError?: (errorMessage: string | null) => void;
    className?: string;
    intlTelInputSettings: IntlTelInputSettings;
};

/**
 * @since 4.6.1 Add intlTelInputSettings to props
 * @since 4.4.0
 */
export default function PhoneInput({ id, value, onChange, onError, className, intlTelInputSettings }: PhoneInputProps) {
    const [country, setCountry] = useState<string>(intlTelInputSettings.initialCountry);

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
    }, [isIntlTelInput]);

    const onChangeNumber = (number: string) => {
        // Safety check to ensure intlTelInputUtils is available before using it
        if (number && window.intlTelInputUtils && !window.intlTelInputUtils.isValidNumber(number, country)) {
            const errorCode = window.intlTelInputUtils.getValidationError(number, country);
            onChange(String(errorCode));
            if (onError) {
                onError(intlTelInputSettings.errorMap[errorCode]);
            }
        } else {
            onChange(number);
            if (onError) {
                onError(null);
            }
        }
    };

    return (
        <div id={id} className={`${styles.phoneInput} ${className || ''}`}>
            <IntlTelInput
                initialValue={value}
                onChangeCountry={setCountry}
                onChangeNumber={onChangeNumber}
                initOptions={{
                    initialCountry: intlTelInputSettings.initialCountry,
                    showSelectedDialCode: intlTelInputSettings.showSelectedDialCode,
                    strictMode: intlTelInputSettings.strictMode,
                    i18n: intlTelInputSettings.i18n,
                    useFullscreenPopup: intlTelInputSettings.useFullscreenPopup,
                    utilsScript: intlTelInputSettings.utilsScriptUrl,
                }}
                inputProps={{
                    'aria-label': __('Phone number', 'give'),
                }}
            />
        </div>
    );
}
