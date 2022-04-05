import {useFormContext, useFormState} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';

/**
 * @unreleased
 *
 * @param name
 * @param label
 * @param fields
 * @returns {JSX.Element}
 */
export default function PaymentDetails({name, label, fields}) {
    const {register} = useFormContext();
    const {errors} = useFormState();
    return (
        <fieldset aria-labelledby={name}>
            <div>
                <h2 id={name}>{label}</h2>
            </div>
            <ul style={{listStyleType: 'none', padding: 0}}>
                {fields.map(({name, label, nodes}) => (
                    <li key={name}>
                        <input {...register('gatewayId', {required: true})} type="radio" value={name} />
                        <label htmlFor={name}> Donate with {label}</label>
                        <div>
                            {nodes.map((field) => {
                                if (field.type === 'html') {
                                    return <div dangerouslySetInnerHTML={{__html: field.html}} key={field.name} />
                                }
                            })}
                        </div>
                    </li>
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
