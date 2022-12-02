import {ErrorMessage} from '@hookform/error-message';
import type {GatewayFieldProps, GatewayOptionProps} from '@givewp/forms/propTypes';

export default function Gateways({inputProps, gateways}: GatewayFieldProps) {
    const {errors} = window.givewp.form.hooks.useFormState();

    return (
        <>
            <ul style={{listStyleType: 'none', padding: 0}}>
                {gateways.map((gateway, index) => (
                    <GatewayOption gateway={gateway} index={index} key={gateway.id} inputProps={inputProps} />
                ))}
            </ul>

            <ErrorMessage
                errors={errors}
                name={'gatewayId'}
                render={({message}) => <span className="give-next-gen__error-message">{message}</span>}
            />
        </>
    );
}

function GatewayOption({gateway, index, inputProps}: GatewayOptionProps) {
    const Fields = gateway.Fields;

    return (
        <li>
            <input type="radio" value={gateway.id} id={gateway.id} defaultChecked={index === 0} {...inputProps} />
            <label htmlFor={gateway.id}> Donate with {gateway.settings.label}</label>
            <div className="givewp-fields-payment-gateway">
                <Fields />
            </div>
        </li>
    );
}
