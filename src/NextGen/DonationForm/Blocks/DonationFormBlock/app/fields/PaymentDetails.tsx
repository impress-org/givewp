import {useFormState} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import PaymentGatewayOption from './PaymentGatewayOption';
import type {Gateway} from "../types/Gateway";

type Props = {
    name: string;
    label: string;
    gateways: Gateway[];
};

export default function PaymentDetails({name, label, gateways}: Props) {
    const {errors} = useFormState();

    return (
        <fieldset aria-labelledby={name}>
            <div>
                <h2 id={name}>{label}</h2>
            </div>
            <ul style={{listStyleType: 'none', padding: 0}}>
                {gateways.map(({id, label, fields}, index) => (
                    <PaymentGatewayOption fields={fields} id={id} label={label} index={index} key={id}/>
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
