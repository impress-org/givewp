import {ChangeEventHandler, forwardRef} from 'react';

import './style.scss';

interface InputProps {
    name?: string;
    value?: string;
    placeholder?: string;
    type?: string;
    onChange?: ChangeEventHandler<HTMLInputElement>;
    disabled?: boolean;
    label?: string;
    [x: string]: any;
}

const Input = forwardRef<HTMLInputElement, InputProps>(
    ({name, value, placeholder, onChange, label, disabled = false, type = 'text', ...rest}, ref) => {

        const Input = () => (
            <input
                ref={ref}
                type={type}
                name={name}
                aria-label={name}
                placeholder={placeholder}
                className="givewp-input"
                onChange={onChange}
                disabled={disabled}
                value={value}
                {...rest}
            />
        )

        if (label) {
            return (
                <label className="label">
                    {Input()}
                    {label}
                </label>
            )
        }

        return Input();
    })

export default Input
