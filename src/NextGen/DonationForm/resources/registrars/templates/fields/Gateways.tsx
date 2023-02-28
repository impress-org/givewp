import {ErrorMessage} from '@hookform/error-message';
import type {GatewayFieldProps, GatewayOptionProps} from '@givewp/forms/propTypes';
import {ErrorBoundary} from 'react-error-boundary';
import {__} from '@wordpress/i18n';

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

export default function Gateways({inputProps, gateways}: GatewayFieldProps) {
    const {errors} = window.givewp.form.hooks.useFormState();

    return (
        <>
            {gateways.length > 0 ? (
                <ul style={{listStyleType: 'none', padding: 0}}>
                    {gateways.map((gateway, index) => (
                        <GatewayOption gateway={gateway} index={index} key={gateway.id} inputProps={inputProps} />
                    ))}
                </ul>
            ) : (
                <em>
                    {__(
                        'No gateways have been enabled yet.  To get started accepting donations, enable a compatible payment gateway in your settings.',
                        'give'
                    )}
                </em>
            )}

            <ErrorMessage
                errors={errors}
                name={'gatewayId'}
                render={({message}) => <span className="give-next-gen__error-message">{message}</span>}
            />
        </>
    );
}

function GatewayOption({gateway, index, inputProps}: GatewayOptionProps) {
    return (
        <li>
            <input type="radio" value={gateway.id} id={gateway.id} defaultChecked={index === 0} {...inputProps} />
            <label htmlFor={gateway.id}> Donate with {gateway.settings.label}</label>
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
