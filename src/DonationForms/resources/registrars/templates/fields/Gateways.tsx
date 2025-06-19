import {ErrorMessage} from '@hookform/error-message';
import type {GatewayFieldProps, GatewayOptionProps} from '@givewp/forms/propTypes';
import {ErrorBoundary} from 'react-error-boundary';
import {__, sprintf} from '@wordpress/i18n';
import {createInterpolateElement, useEffect, useMemo} from '@wordpress/element';
import cx from 'classnames';
import {isDonationTypeSubscription} from '@givewp/forms/types';

interface EmptyMessageProps {
    message: string;
}

interface GatewayMissingMessageProps {
    donationAmountMinimumNotReached?: boolean;
    currencyNotSupported?: boolean;
    subscriptionNotSupported?: boolean;
}

interface GatewayFieldsErrorFallbackProps {
    error: Error;
    resetErrorBoundary: () => void;
}

/**
 * Empty message component displayed when no payment gateways are available.
 *
 * @since 3.20.0
 */
function EmptyMessage({message}: EmptyMessageProps) {
    return (
        <div className="givewp-fields-gateways__gateway--empty">
            <p>
                <b>{__('Payment options are not available:', 'give')}</b>
            </p>

            <p>
                <em>{message}</em>
            </p>
        </div>
    );
}

/**
 * Component that displays contextual messages when no gateways are available.
 * Handles different scenarios like minimum donation amount, currency support, and subscription support.
 *
 * @since 3.20.0 updated message to account for minimum donation amount
 * @since 3.0.0
 */
function GatewayMissingMessage({
    donationAmountMinimumNotReached,
    currencyNotSupported,
    subscriptionNotSupported,
}: GatewayMissingMessageProps) {
    let message = __(
        'No gateways have been enabled yet.  To get started accepting donations, enable a compatible payment gateway in your settings.',
        'give'
    );

    if (donationAmountMinimumNotReached) {
        message = __('Donation amount must be greater than zero.', 'give');
    } else if (currencyNotSupported) {
        message = __(
            'The selected currency is not supported by any of the available payment gateways.  Please select a different currency or contact the site administrator for assistance.',
            'give'
        );
    } else if (subscriptionNotSupported) {
        message = __(
            'No gateways support recurring payments. Please select a different payment gateway or contact the site administrator for assistance.',
            'give'
        );
    }

    return <EmptyMessage message={message} />;
}

/**
 * Error fallback component for gateway fields that fail to render.
 * Provides user-friendly error message and reload functionality.
 *
 * @since 3.0.0
 */
function GatewayFieldsErrorFallback({error, resetErrorBoundary}: GatewayFieldsErrorFallbackProps) {
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
 * Notice component displayed when the donation form is in test mode.
 * Informs users that no live donations will be processed.
 *
 * @since 3.0.0
 */
const TestModeNotice = () => {
    return (
        <div className="givewp-test-mode-notice">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    fillRule="evenodd"
                    clipRule="evenodd"
                    d="M12 1C5.92487 1 1 5.92487 1 12C1 18.0751 5.92487 23 12 23C18.0751 23 23 18.0751 23 12C23 5.92487 18.0751 1 12 1ZM12 7C11.4477 7 11 7.44772 11 8C11 8.55228 11.4477 9 12 9H12.01C12.5623 9 13.01 8.55228 13.01 8C13.01 7.44772 12.5623 7 12.01 7H12ZM13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12V16C11 16.5523 11.4477 17 12 17C12.5523 17 13 16.5523 13 16V12Z"
                    fill="#F29718"
                />
            </svg>{' '}
            <p>
                {createInterpolateElement(
                    __(
                        'Test mode is <strong>enabled</strong>. While in test mode no live donations are processed.',
                        'give'
                    ),
                    {
                        strong: <strong />,
                    }
                )}
            </p>
        </div>
    );
};

/**
 * Main Gateways component that handles payment gateway selection and display.
 *
 * This component:
 * - Filters available gateways based on donation amount, currency, and subscription support
 * - Handles currency switcher settings to show only compatible gateways
 * - Automatically selects appropriate default gateway when options change
 * - Displays contextual messages when no gateways are available
 * - Shows test mode notice when applicable
 *
 * @since 4.4.0 filter gateways based on donation type
 * @since 3.0.0
 */
export default function Gateways({isTestMode, defaultValue, inputProps, gateways}: GatewayFieldProps) {
    const {useFormState, useWatch, useFormContext, useDonationFormSettings} = window.givewp.form.hooks;
    const {errors} = useFormState();
    const {setValue} = useFormContext();
    const {currencySwitcherSettings} = useDonationFormSettings();

    // Watch form values that affect gateway availability
    const donationAmount = useWatch({name: 'amount'});
    const currency = useWatch({name: 'currency'});
    const activeGatewayId = useWatch({name: 'gatewayId'});
    const donationType = useWatch({name: 'donationType'});
    const isSubscription = isDonationTypeSubscription(donationType);

    const donationAmountMinimumNotReached = donationAmount === 0;

    /**
     * Filter gateway options based on currency switcher settings.
     * If currency switcher is enabled, only show gateways that support the selected currency.
     */
    const gatewayOptionsWithCurrencySettings = useMemo(() => {
        if (currencySwitcherSettings.length <= 1) {
            return gateways;
        }

        const currencySwitcherSetting = currencySwitcherSettings.find(({id}) => id === currency);

        if (!currencySwitcherSetting) {
            return [];
        }

        return gateways.filter(({id}) => currencySwitcherSetting.gateways.includes(id));
    }, [currency]);

    /**
     * Filter gateways that support subscription/recurring donations.
     */
    const gatewayOptionsWithSubscriptionSupport = useMemo(() => {
        return gatewayOptionsWithCurrencySettings.filter(({supportsSubscriptions}) => supportsSubscriptions);
    }, [gatewayOptionsWithCurrencySettings]);

    /**
     * Final filtered gateway options based on all criteria:
     * - Donation amount must be greater than zero
     * - For subscriptions, gateway must support recurring payments
     * - Gateway must support the selected currency
     */
    const gatewayOptions = useMemo(() => {
        if (donationAmountMinimumNotReached) {
            return [];
        }

        if (isSubscription) {
            return gatewayOptionsWithSubscriptionSupport.length > 0 ? gatewayOptionsWithSubscriptionSupport : [];
        }

        return gatewayOptionsWithCurrencySettings.length > 0 ? gatewayOptionsWithCurrencySettings : [];
    }, [
        donationAmountMinimumNotReached,
        gatewayOptionsWithSubscriptionSupport,
        gatewayOptionsWithCurrencySettings,
        isSubscription,
    ]);

    /**
     * Automatically set the selected gateway when available options change.
     * - If default gateway is still available, keep it selected
     * - Otherwise, select the first available gateway
     * - If no gateways available, clear the selection
     */
    useEffect(() => {
        if (gatewayOptions.length > 0) {
            const optionsDefaultValue = gatewayOptions.find(option => option.id === defaultValue)
                ? defaultValue
                : gatewayOptions[0].id;

            setValue(inputProps.name, optionsDefaultValue);
        } else {
            setValue(inputProps.name, null);
        }
    }, [gatewayOptions]);

    return (
        <>
            {gatewayOptions.length > 0 ? (
                <>
                    {isTestMode && <TestModeNotice />}
                    <ul className="givewp-fields-gateways__list" style={{listStyleType: 'none', padding: 0}}>
                        {gatewayOptions.map((gateway) => (
                            <GatewayOption
                                gateway={gateway}
                                defaultChecked={gateway.id === defaultValue}
                                key={gateway.id}
                                inputProps={inputProps}
                                isActive={gateway.id === activeGatewayId}
                            />
                        ))}
                    </ul>
                </>
            ) : (
                <GatewayMissingMessage
                    donationAmountMinimumNotReached={donationAmountMinimumNotReached}
                    currencyNotSupported={gatewayOptionsWithCurrencySettings.length === 0 && !donationAmountMinimumNotReached}
                    subscriptionNotSupported={isSubscription && gatewayOptionsWithSubscriptionSupport.length === 0}
                />
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
 * Individual gateway option component that renders a radio button with gateway information.
 *
 * Features:
 * - Radio button input for gateway selection
 * - Gateway-specific styling and icons
 * - Dynamic icon selection based on gateway type
 * - Error boundary for gateway-specific fields
 * - Conditional rendering of gateway fields when active
 *
 * @since 3.0.0
 */
function GatewayOption({gateway, defaultChecked, inputProps, isActive}: GatewayOptionProps) {
    const gatewayClass = `givewp-fields-gateways__gateway--${gateway.id}`;
    const className = cx('givewp-fields-gateways__gateway', {
        [gatewayClass]: true,
        'givewp-fields-gateways__gateway--active': isActive,
    });

    // Determine appropriate icon based on gateway type
    let fontAwesomeClass = 'fa-solid fa-gear';
    if (gateway.id.includes('stripe') || gateway.id.includes('card')) {
        fontAwesomeClass = 'fa-solid fa-credit-card';
    } else if (gateway.id.includes('paypal')) {
        fontAwesomeClass = 'fa-brands fa-paypal';
    } else if (gateway.id.includes('test')) {
        fontAwesomeClass = 'fa-solid fa-screwdriver-wrench';
    }

    return (
        <li className={className}>
            <label>
                <input
                    type="radio"
                    value={gateway.id}
                    id={gateway.id}
                    defaultChecked={defaultChecked}
                    {...inputProps}
                />
                <span className="givewp-fields-gateways__gateway__label">
                    {sprintf(__('Donate with %s', 'give'), gateway.label)}
                </span>
                <i className={`givewp-fields-gateways__gateway__icon ${fontAwesomeClass}`}></i>
            </label>
            <div className="givewp-fields-gateways__gateway__fields">
                <ErrorBoundary
                    FallbackComponent={GatewayFieldsErrorFallback}
                    onReset={() => {
                        window.location.reload();
                    }}
                >
                    {isActive && <gateway.Fields />}
                </ErrorBoundary>
            </div>
        </li>
    );
}
