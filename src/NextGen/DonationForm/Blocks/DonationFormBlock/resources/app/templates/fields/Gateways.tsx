import {UseFormRegisterReturn, useFormState} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import {useGiveDonationFormStore} from '../../store';
import {FieldProps} from '@givewp/forms/propTypes';
import {Gateway} from '@givewp/forms/types';

export default function Gateways({inputProps}: FieldProps) {
    const {errors} = useFormState();
    const {gateways} = useGiveDonationFormStore();

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

type GatewayOptionProps = {
    inputProps: UseFormRegisterReturn;
    gateway: Gateway;
    index: number;
};

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
