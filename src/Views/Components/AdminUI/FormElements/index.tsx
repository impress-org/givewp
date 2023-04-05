import React from 'react';

import ReactSelect from 'react-select';
import AsyncSelect from 'react-select/async';
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

export type InputFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    label: string;
};

const TextInputField = React.forwardRef<HTMLInputElement, InputFieldProps>(
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
    placeholder: string;
    label: string;
    currency: string;
    defaultValue: number;
    handleCurrencyChange: (value: number) => void;
};

export function CurrencyInputField({
    defaultValue,
    placeholder,
    handleCurrencyChange,
    currency,
    label,
}: CurrencyInputFieldProps) {
    return (
        <label>
            {label && <span className={styles.fieldLabel}>{label}</span>}
            <div className={cx(styles.textFieldContainer, styles.currencyField, {})}>
                <CurrencyInput
                    name={'currency-input-field'}
                    allowNegativeValue={false}
                    onValueChange={(value, name) => {
                        handleCurrencyChange(Number(value));
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
                            className={styles.reactSelectContainer}
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

export type AsyncSelectDropdownFieldProps = {
    defaultOptions: Array<{value: any; label: string}>;
    loadOptions: (inputValue: string, callback: (option) => void) => void;
    name: string;
    isSearchable: boolean;
    isClearable: boolean;
    placeholder: string;
    label: string;
    styleConfig?: object;
    isLoading?: boolean;
};

export function AsyncSelectDropdownField({
    defaultOptions,
    loadOptions,
    styleConfig,
    name,
    isSearchable,
    isClearable,
    placeholder,
    isLoading,
    label,
}: AsyncSelectDropdownFieldProps) {
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
                        <AsyncSelect
                            ref={ref}
                            name={name}
                            defaultValue={defaultOptions?.find((option) => option.value === value)}
                            defaultOptions={defaultOptions}
                            loadOptions={loadOptions}
                            onChange={(selectedOption) => onChange(selectedOption.value)}
                            isClearable={isClearable}
                            isSearchable={isSearchable}
                            placeholder={isLoading ? __('Options are loading...') : placeholder ?? ''}
                            components={{
                                IndicatorSeparator: () => null,
                            }}
                            styles={styleConfig}
                            className={styles.reactSelectContainer}
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

/**
 *
 * @unreleased
 */
export type TextAreaProps = InputFieldProps & {
    defaultValue: string;
};

export const TextAreaField = React.forwardRef<HTMLTextAreaElement, TextAreaProps>(
    ({name, placeholder, label, defaultValue, ...props}, ref) => {
        return (
            <label>
                {label && <span className={styles.fieldLabel}>{label}</span>}{' '}
                <div className={cx(styles.textFieldContainer)}>
                    <textarea
                        defaultValue={defaultValue}
                        className={cx(styles.textAreaField)}
                        ref={ref}
                        name={name}
                        placeholder={placeholder}
                        {...props}
                    />
                </div>
            </label>
        );
    }
);
