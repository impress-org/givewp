import {ErrorMessage} from '@hookform/error-message';
import type {GatewayFieldProps, GatewayOptionProps} from '@givewp/forms/propTypes';
import {ErrorBoundary} from 'react-error-boundary';
import {__} from '@wordpress/i18n';
import {useEffect, useMemo} from 'react';

/**
 * @unreleased
 */
function GatewayMissingMessage({currencyNotSupported}: {currencyNotSupported?: boolean}) {
    return (
        <em>
            {currencyNotSupported
                ? __(
                    'The selected currency is not supported by any of the available payment gateways.  Please select a different currency or contact the site administrator for assistance.',
                    'give'
                )
                : __(
                    'No gateways have been enabled yet.  To get started accepting donations, enable a compatible payment gateway in your settings.',
                    'give'
                )}
        </em>
    );
}

/**
 * @since 0.1.0
 */
function GatewayFieldsErrorFallback({error, resetErrorBoundary}) {
    return (
        <div role="alert">
            <p>
                {__(
                    'An error occurred while loading the gateway fields.  Please notify the site administrator.  The error message is:',
                    'give'
                )}
            </p>
            <pre style={{padding: '0.5rem'}}>{error.message}</pre>
            <button type="button" onClick={resetErrorBoundary}>
                {__('Reload form', 'give')}
            </button>
        </div>
    );
}

/**
 * @unreleased update to support currency switcher settings
 * @since 0.1.0
 */
export default function Gateways({defaultValue, inputProps, gateways}: GatewayFieldProps) {
    const {useFormState, useWatch, useFormContext, useDonationFormSettings} = window.givewp.form.hooks;
    const {errors} = useFormState();
    const {setValue} = useFormContext();
    const {currencySwitcherSettings} = useDonationFormSettings();

    const currency = useWatch({name: 'currency'});

    // filter gateway options if currency switcher settings are present
    const gatewayOptions = useMemo(() => {
        if (currencySwitcherSettings.length <= 1) {
            return gateways;
        }

        const currencySwitcherSetting = currencySwitcherSettings.find(({id}) => id === currency);

        if (!currencySwitcherSetting) {
            return [];
        }

        return gateways.filter(({id}) => currencySwitcherSetting.gateways.includes(id));
    }, [currency]);

    // reset the default /selected gateway based on the gateway options available
    useEffect(() => {
        if (gatewayOptions.length > 0) {
            const optionsDefaultValue = gatewayOptions.includes(defaultValue) ? defaultValue : gatewayOptions[0].id;

            setValue(inputProps.name, optionsDefaultValue);
        } else {
            setValue(inputProps.name, null);
        }
    }, [gatewayOptions]);

    return (
        <>
            {gatewayOptions.length > 0 ? (
                <ul style={{listStyleType: 'none', padding: 0}}>
                    {gatewayOptions.map((gateway, index) => (
                        <GatewayOption
                            gateway={gateway}
                            defaultChecked={gateway.id === defaultValue}
                            key={gateway.id}
                            inputProps={inputProps}
                        />
                    ))}
                </ul>
            ) : (
                <GatewayMissingMessage currencyNotSupported={currencySwitcherSettings.length > 1} />
            )}

            <ErrorMessage
                errors={errors}
                name={'gatewayId'}
                render={({message}) => <span className="give-next-gen__error-message">{message}</span>}
            />
        </>
    );
}

/**
 * @unreleased replace index prop with defaultChecked
 * @since 0.1.0
 */
function GatewayOption({gateway, defaultChecked, inputProps}: GatewayOptionProps) {
    return (
        <li>
            <input type="radio" value={gateway.id} id={gateway.id} defaultChecked={defaultChecked} {...inputProps} />
            <label htmlFor={gateway.id}> Donate with {gateway.label}</label>
            <div className="givewp-fields-payment-gateway">
                <ErrorBoundary
                    FallbackComponent={GatewayFieldsErrorFallback}
                    onReset={() => {
                        window.location.reload();
                    }}
                >
                    <gateway.Fields />
                </ErrorBoundary>
            </div>
        </li>
    );
}
