import {useFormState} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import PaymentGatewayOption from './PaymentGatewayOption';
import type {Gateway} from '@givewp/forms/types';

type Props = {
    name: string;
    label: string;
    gateways: Gateway[];
};

export default function PaymentDetails({name, label, gateways}: Props) {
    const {errors} = useFormState();

    return (
        <section className={`givewp-layouts givewp-layouts-section givewp-layouts-section-payment-gateways`}>
            <fieldset aria-labelledby={name}>
                <div>
                    <legend id={name}>{label}</legend>
                </div>
                <ul style={{listStyleType: 'none', padding: 0}}>
                    {gateways.map((gateway, index) => (
                        <PaymentGatewayOption gateway={gateway} index={index} key={gateway.id}/>
                    ))}
                </ul>

                <ErrorMessage
                    errors={errors}
                    name={'gatewayId'}
                    render={({message}) => <span className="give-next-gen__error-message">{message}</span>}
                />
            </fieldset>
        </section>
    );
}
