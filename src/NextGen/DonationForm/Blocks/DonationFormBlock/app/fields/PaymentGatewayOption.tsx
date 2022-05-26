import {useFormContext} from 'react-hook-form';
import {Gateway} from "../types/Gateway";

type Props = {
    gateway: Gateway;
    index: number;
};

export default function PaymentGatewayOption({gateway, index}: Props) {
    const {register} = useFormContext();

    return (
        <li>
            <input
                {...register('gatewayId', {required: true})}
                type="radio"
                value={gateway.id}
                defaultChecked={index === 0}
            />
            <label htmlFor={gateway.id}> Donate with {gateway.label}</label>
            <div style={{paddingBottom: '20px'}}>
                {gateway.fields()}
            </div>
        </li>
    );
}
