import {ChangeEventHandler, forwardRef} from 'react';

import styles from './style.module.scss';

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
                className={styles.input}
                onChange={onChange}
                disabled={disabled}
                value={value}
                {...rest}
            />
        )

        if (label) {
            return (
                <label className={styles.label}>
                    {Input()}
                    {label}
                </label>
            )
        }

        return Input();
    })

export default Input
