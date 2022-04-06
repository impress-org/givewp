import {useFormState} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import PaymentGatewayOption from './PaymentGatewayOption';
import {string} from 'joi';
import PaymentGateway from '../value-objects/PaymentGateway';

interface GatewayNode {
    name: PaymentGateway;
    label: string;
}

type Props = {
    name: string;
    label: string;
    fields: object[];
};

export default function PaymentDetails({name, label, fields}: Props) {
    const {errors} = useFormState();

    return (
        <fieldset aria-labelledby={name}>
            <div>
                <h2 id={name}>{label}</h2>
            </div>
            <ul style={{listStyleType: 'none', padding: 0}}>
                {fields.map(({name, label}: GatewayNode, index) => (
                    <PaymentGatewayOption name={name} label={label} index={index} key={name} />
                ))}
            </ul>

            <ErrorMessage
                errors={errors}
                name={'gatewayId'}
                render={({message}) => <span className="give-next-gen__error-message">{message}</span>}
            />
        </fieldset>
    );
}
