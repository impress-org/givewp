import React from 'react';

import ReactSelect from 'react-select';
import {Controller} from 'react-hook-form';
import {__} from '@wordpress/i18n';
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

const Form: React.FC<HTMLFormElement | FormElementProps> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

export {Form};

/**
 *
 * @unreleased
 */

export type TextInputFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    label: string;
};

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

export {TextInputField};

/**
 *
 * @unreleased
 */

export type CurrencyInputFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    label: string;
    currency: string;
    defaultValue: number;
    handleCurrencyChange: () => void;
};

export function CurrencyInputField({defaultValue, placeholder, handleCurrencyChange, currency, label}) {
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
                    decimalSeparator={'.'}
                    groupSeparator={','}
                    decimalScale={2}
                    placeholder={placeholder}
                    defaultValue={defaultValue}
                />
            </div>
        </label>
    );
}

/**
 *
 * @unreleased
 */

export type SelectDropdownFieldProps = {
    options: Array<{value: any; label: string}>;
    name: string;
    isSearchable: boolean;
    isClearable: boolean;
    placeholder: string;
    label: string;
    styleConfig?: object;
    isLoading?: boolean;
};

export function SelectDropdownField({
    options,
    styleConfig,
    name,
    isSearchable,
    isClearable,
    placeholder,
    isLoading,
    label,
}: SelectDropdownFieldProps) {
    return (
        <>
            <label htmlFor={name} className={styles.fieldLabel}>
                <span>{label}</span>
                <Controller
                    name={name}
                    render={({
                        field: {onChange, onBlur, value, name, ref},
                        fieldState: {invalid, isTouched, isDirty, error},
                        formState,
                    }) => (
                        <ReactSelect
                            ref={ref}
                            name={name}
                            value={options?.find((option) => option.value === value)}
                            options={options}
                            onChange={(selectedOption) => onChange(selectedOption.value)}
                            isClearable={isClearable}
                            isSearchable={isSearchable}
                            placeholder={isLoading ? __('Options are loading...') : placeholder ?? ''}
                            components={{
                                IndicatorSeparator: () => null,
                            }}
                            styles={styleConfig}
                        />
                    )}
                />
            </label>
        </>
    );
}

/**
 *
 * @unreleased
 */

export type DisabledTextFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    value: string;
    label: string;
};

export function DisabledTextField({name, type, placeholder, label, value}: DisabledTextFieldProps) {
    return (
        <label>
            {label && <span className={styles.fieldLabel}>{label}</span>}
            <div className={cx(styles.textFieldContainer)}>
                <input
                    className={styles.disabled}
                    disabled
                    name={name}
                    value={value}
                    type={type}
                    placeholder={placeholder}
                />
            </div>
        </label>
    );
}
