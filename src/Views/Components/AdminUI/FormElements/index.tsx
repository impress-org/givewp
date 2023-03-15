import React from 'react';

import cx from 'classnames';

import {FormElementProps, TextInputFieldProps} from './types';

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
    ({name, type, placeholder, label, asCurrencyField, ...props}, ref) => {
        return (
            <label>
                {label && <span className={styles.fieldLabel}>{label}</span>}
                <div
                    className={cx(styles.textFieldContainer, {
                        [styles.currencyField]: asCurrencyField,
                    })}
                >
                    {asCurrencyField ? (
                        <CurrencyInput
                            ref={ref}
                            name={name}
                            type={type}
                            placeholder={placeholder}
                            decimalsLimit={2}
                            intlConfig={{
                                locale: navigator.language || (navigator.languages || ['en'])[0],
                                currency: 'USD',
                            }}
                            {...props}
                        />
                    ) : (
                        <input ref={ref} name={name} type={type} placeholder={placeholder} {...props} />
                    )}
                </div>
            </label>
        );
    }
);

/**
 *
 * @unreleased
 */

export {Form, TextInputField};
