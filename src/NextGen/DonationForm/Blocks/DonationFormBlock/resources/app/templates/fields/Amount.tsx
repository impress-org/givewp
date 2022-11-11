import {__} from '@wordpress/i18n';
import {useMemo} from 'react';
import classNames from 'classnames';
import type {FieldProps} from '@givewp/forms/propTypes';

interface AmountProps extends FieldProps {
    levels: Number[];
    allowCustomAmount: boolean;
}

export default function Amount({
    name,
    Label,
    ErrorMessage,
    inputProps,
    levels,
    allowCustomAmount,
    fieldError,
}: AmountProps) {
    const {useWatch} = window.givewp.form;
    const currency = useWatch({name: 'currency'});
    const formatter = useMemo(
        () =>
            new Intl.NumberFormat(navigator.language, {
                style: 'currency',
                currency: currency,
            }),
        [currency, navigator.language]
    );

    return (
        <>
            <div className="givewp-fields-amount__amount--container">
                <label
                    className="givewp-fields-amount__input--label"
                    htmlFor={name}
                    aria-labelledby={name}
                    style={{display: 'none'}}
                >
                    <Label />
                </label>
                <div className={classNames('givewp-fields-amount__input--container', {invalid: fieldError})}>
                    <span className="givewp-fields-amount__input--currency-symbol">
                        {formatter.formatToParts().find(({type}) => type === 'currency').value}
                    </span>
                    <input
                        className="givewp-fields-amount__input"
                        type={allowCustomAmount ? 'text' : 'hidden'}
                        aria-invalid={fieldError ? 'true' : 'false'}
                        id={name}
                        inputMode="numeric"
                        {...inputProps}
                    />
                </div>

                <ErrorMessage />
            </div>
            <AmountButtons name={name} currency={currency} levels={levels} />
        </>
    );
}

function AmountButtons({name, currency, levels}: {name: string; currency: string; levels: Number[]}) {
    const {useFormContext, useWatch} = window.givewp.form;
    const {setValue, setFocus} = useFormContext();
    const amount = useWatch({name});
    const formatter = useMemo(
        () =>
            new Intl.NumberFormat(navigator.language, {
                style: 'currency',
                currency: currency,
            }),
        [currency, navigator.language]
    );

    return (
        <div className="givewp-fields-amount__levels--container">
            {levels.map((levelAmount, index) => {
                const label = formatter.format(Number(levelAmount));
                const selected = levelAmount === Number(amount);
                return (
                    <button
                        className={classNames('givewp-fields-amount__level', {
                            'givewp-fields-amount__level--selected': selected,
                        })}
                        type="button"
                        onClick={() => setValue(name, levelAmount)}
                        key={index}
                    >
                        {label}
                    </button>
                );
            })}

            <button
                className={classNames('givewp-fields-amount__level', 'givewp-fields-amount__level--custom', {
                    'givewp-fields-amount__level--selected': !levels.includes(Number(amount)),
                })}
                type="button"
                onClick={() => {
                    setValue(name, null);
                    setFocus('amount', {shouldSelect: true});
                }}
                key="custom"
            >
                {__('Custom Amount', 'give')}
            </button>
        </div>
    );
}
