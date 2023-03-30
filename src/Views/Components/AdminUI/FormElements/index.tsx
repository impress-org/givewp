import React from 'react';

import cx from 'classnames';

import CurrencyInput from 'react-currency-input-field';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export type FormElementProps = {
    children: React.ReactNode;
    onSubmit: React.FormEventHandler<HTMLFormElement>;
    id: string;
};

export const Form: React.FC<HTMLFormElement | FormElementProps> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

/**
 *
 * @unreleased
 */

export type InputFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    label: string;
};

export const TextInputField = React.forwardRef<HTMLInputElement, InputFieldProps>(
    ({name, type, placeholder, label, ...props}, ref) => {
        return (
            <FieldLabel label={label}>
                <div className={cx(styles.textFieldContainer)}>
                    <input ref={ref} name={name} type={type} placeholder={placeholder} {...props} />
                </div>
            </FieldLabel>
        );
    }
);

/**
 *
 * @unreleased
 */

export type CurrencyInputFieldProps = InputFieldProps & {
    currency: string;
    defaultValue: number;
    handleCurrencyChange: () => void;
};

export function CurrencyInputField({defaultValue, placeholder, handleCurrencyChange, currency, label}) {
    return (
        <FieldLabel label={label}>
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
                    decimalSeparator={'.'}
                    groupSeparator={','}
                    decimalScale={2}
                    placeholder={placeholder}
                    defaultValue={defaultValue}
                />
            </div>
        </FieldLabel>
    );
}

export type LabelProps = {
    label: string;
    children: React.ReactNode;
};

export function FieldLabel({label, children}) {
    return (
        <label>
            {label && <span className={styles.fieldLabel}>{label}</span>}
            {children}
        </label>
    );
}
