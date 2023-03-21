import React from 'react';

import cx from 'classnames';

import {CurrencyInputFieldProps, FormElementProps, TextInputFieldProps} from './types';

import styles from './style.module.scss';
import CurrencyInput from 'react-currency-input-field';

/**
 *
 * @unreleased
 */

const Form: React.FC<HTMLFormElement | FormElementProps> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

/**
 *
 * @unreleased
 */

const TextInputField = React.forwardRef<HTMLInputElement, TextInputFieldProps>(
    ({name, type, placeholder, label, ...props}, ref) => {
        return (
            <label>
                {label && <span className={styles.fieldLabel}>{label}</span>}
                <div className={cx(styles.textFieldContainer)}>
                    <input ref={ref} name={name} type={type} placeholder={placeholder} {...props} />
                </div>
            </label>
        );
    }
);

/**
 *
 * @unreleased
 */

const CurrencyInputField = React.forwardRef<HTMLInputElement, CurrencyInputFieldProps>(
    ({name, type, placeholder, currency, label, ...props}, ref) => {
        return (
            <label>
                {label && <span className={styles.fieldLabel}>{label}</span>}
                <div className={cx(styles.textFieldContainer, styles.currencyField, {})}>
                    <CurrencyInput
                        ref={ref}
                        name={name}
                        type={type}
                        placeholder={placeholder}
                        decimalsLimit={2}
                        intlConfig={{
                            locale: navigator.language || (navigator.languages || ['en'])[0],
                            currency: currency,
                        }}
                        {...props}
                    />
                </div>
            </label>
        );
    }
);

export {Form, TextInputField, CurrencyInputField};
