import {useFormContext} from 'react-hook-form';
import usePaymentGatewayFields from '../hooks/usePaymentGatewayFields';
import PaymentGateway from '../value-objects/PaymentGateway';

type Props = {
    name: PaymentGateway;
    label: string;
    index: number;
};

export default function PaymentGatewayOption({name, label, index}: Props) {
    const {register} = useFormContext();
    const Fields = usePaymentGatewayFields(name);

    return (
        <li>
            <input
                {...register('gatewayId', {required: true})}
                type="radio"
                value={name}
                defaultChecked={index === 0}
            />
            <label htmlFor={name}> Donate with {label}</label>
            <div style={{paddingBottom: '20px'}}>
                <Fields />
            </div>
        </li>
    );
}
