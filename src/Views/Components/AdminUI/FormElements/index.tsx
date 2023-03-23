import React from 'react';

import cx from 'classnames';

import {FormElementProps, TextInputFieldProps} from './types';
import CurrencyInput from 'react-currency-input-field';

import styles from './style.module.scss';

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

const CurrencyInputField = ({defaultValue, placeholder, handleCurrencyChange, currency, label}) => {
    return (
        <label>
            {label && <span className={styles.fieldLabel}>{label}</span>}
            <div className={cx(styles.textFieldContainer, styles.currencyField, {})}>
                <CurrencyInput
                    name={'currency-input-field'}
                    allowNegativeValue={false}
                    onValueChange={(value, name) => {
                        handleCurrencyChange(value);
                    }}
                    intlConfig={{
                        locale: navigator.language,
                        currency: currency,
                    }}
                    placeholder={placeholder}
                    defaultValue={defaultValue}
                />
            </div>
        </label>
    );
};

export {Form, TextInputField, CurrencyInputField};
