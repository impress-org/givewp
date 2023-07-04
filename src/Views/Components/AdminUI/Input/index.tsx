import {ChangeEventHandler, memo} from 'react';

import styles from './style.module.scss';

interface InputProps {
    name?: string;
    value?: string;
    placeholder?: string;
    type?: string;
    onChange?: ChangeEventHandler<HTMLInputElement>;
    disabled?: boolean;
}

const Input = memo(({name, placeholder, onChange, disabled = false, type = 'text', ...rest}: InputProps) => {
    return (
        <input
            {...rest}
            type={type}
            name={name}
            aria-label={name}
            placeholder={placeholder}
            className={styles.input}
            onChange={onChange}
            disabled={disabled}
        />
    )
})

export default Input
