import {useFormContext} from 'react-hook-form';

type Props = {
    id: string;
    label: string;
    index: number;
    fields: Function;
};

export default function PaymentGatewayOption({id, label, fields: Fields, index}: Props) {
    const {register} = useFormContext();

    return (
        <li>
            <input
                {...register('gatewayId', {required: true})}
                type="radio"
                value={id}
                defaultChecked={index === 0}
            />
            <label htmlFor={id}> Donate with {label}</label>
            <div style={{paddingBottom: '20px'}}>
                <Fields/>
            </div>
        </li>
    );
}
