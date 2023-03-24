import {useRef} from 'react';
import {__} from '@wordpress/i18n';
import {AmpmProps, NumberFieldProps} from '../types';

export function NumberField({state, setState, label, id, min, max}: NumberFieldProps) {
    const inputRef = useRef(null);

    const handleInputChange = () => {
        if (inputRef.current.value > max) {
            inputRef.current.value = max;
            alert(__('Please ensure your values respect a 12 hour format', 'give'));
        }
    };

    return (
        <>
            <label hidden htmlFor={id}>
                {label}
            </label>
            <input
                ref={inputRef}
                onChange={(event) => {
                    setState(event.target.value);
                }}
                onInput={handleInputChange}
                id={id}
                name={id}
                defaultValue={state}
                type={'number'}
                min={min}
                max={max}
            />
        </>
    );
}

export function AmpmField({setState, state}: AmpmProps) {
    return (
        <>
            <label hidden htmlFor={'give-payment-time-am-pm'}>
                {__('Time of day am or pm')}
            </label>
            <select
                onChange={(event) => {
                    setState(event.target.value);
                }}
                id="give-payment-time-am-pm"
                name="ampm"
                defaultValue={state}
            >
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>
        </>
    );
}
