import {useFormContext} from 'react-hook-form';
import {Gateway} from '@givewp/forms/types';

type Props = {
    gateway: Gateway;
    index: number;
};

export default function PaymentGatewayOption({gateway, index}: Props) {
    const {register} = useFormContext();
    const Fields = gateway.Fields;

    return (
        <li>
            <input
                {...register('gatewayId', {required: true})}
                type="radio"
                value={gateway.id}
                defaultChecked={index === 0}
            />
            <label htmlFor={gateway.id}> Donate with {gateway.settings.label}</label>
            <div style={{paddingBottom: '20px'}}>
                <Fields />
            </div>
        </li>
    );
}
