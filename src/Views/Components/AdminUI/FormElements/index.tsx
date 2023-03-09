import React from 'react';

import cx from 'classnames';

import {Form, TextInputField} from '../types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

const Form: React.FC<HTMLFormElement | Form> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

/**
 *
 * @unreleased
 */

const TextInputField = React.forwardRef<HTMLInputElement, TextInputField>(
    ({name, type, placeholder, label, asCurrencyField, ...props}, ref) => {
        return (
            <label>
                {label && <span className={styles.fieldLabel}>{label}</span>}
                <div className={styles.textFieldContainer}>
                    <input
                        ref={ref}
                        name={name}
                        className={cx({
                            [styles.currencyField]: asCurrencyField,
                        })}
                        type={type}
                        placeholder={placeholder}
                        {...props}
                    />
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
