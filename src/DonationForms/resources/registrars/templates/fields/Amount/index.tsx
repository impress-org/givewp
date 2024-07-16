import {useCallback} from '@wordpress/element';
import type {AmountProps} from '@givewp/forms/propTypes';
import CustomAmount from './CustomAmount';
import {useEffect, useState} from 'react';
import {getAmountLevelsWithCurrencySettings, getDefaultAmountWithCurrencySettings} from './withCurrencySettings';
import DonationAmountCurrency from './DonationAmountCurrency';
import DonationAmountLevels from './DonationAmountLevels';

/**
 * @since 3.12.0 Update default level when having distinct default currency
 * @since 3.0.0
 */
export default function Amount({
    name,
    defaultValue,
    Label,
    ErrorMessage,
    inputProps,
    fieldError,
    allowLevels,
    levels,
    fixedAmountValue,
    allowCustomAmount,
    messages,
}: AmountProps) {
    const isFixedAmount = !allowLevels;
    const [customAmountValue, setCustomAmountValue] = useState<string>(
        isFixedAmount ? fixedAmountValue.toString() : ''
    );
    const {useWatch, useFormContext, useDonationFormSettings} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const {currencySwitcherSettings} = useDonationFormSettings();

    const currency = useWatch({name: 'currency'});

    useEffect(() => {
        if (!isFixedAmount) {
            const defaultAmount = getDefaultAmountWithCurrencySettings(
                levels,
                defaultValue,
                currency,
                currencySwitcherSettings
            );
            setValue(name, defaultAmount);
        }
    }, []);

    const getAmountLevels = useCallback(() => {
        if (currencySwitcherSettings.length <= 1) {
            return levels;
        }

        return getAmountLevelsWithCurrencySettings(levels, currency, currencySwitcherSettings);
    }, [currency]);

    const resetCustomAmount = useCallback(() => {
        if (customAmountValue !== '') {
            setCustomAmountValue('');
        }
    }, [customAmountValue]);

    const updateCustomAmount = useCallback(
        (amount: number) => {
            if (customAmountValue !== '') {
                setCustomAmountValue(amount.toFixed(2));
            }
        },
        [customAmountValue]
    );

    return (
        <>
            <div className="givewp-fields-amount__input-label-container">
                <label className="givewp-fields-amount__input-label" htmlFor={name} aria-labelledby={name}>
                    <Label />
                </label>

                <DonationAmountCurrency
                    currencySettings={currencySwitcherSettings}
                    onCurrencyAmountChange={updateCustomAmount}
                />
            </div>

            {allowLevels && (
                <DonationAmountLevels
                    name={name}
                    currency={currency}
                    levels={getAmountLevels()}
                    onLevelClick={(levelAmount) => {
                        resetCustomAmount();
                        setValue(name, levelAmount);
                    }}
                />
            )}

            {allowCustomAmount && (
                <CustomAmount
                    fieldError={fieldError}
                    defaultValue={customAmountValue}
                    currency={currency}
                    value={customAmountValue}
                    onValueChange={(value) => {
                        setCustomAmountValue(value);
                        setValue(name, Number(value) ?? null);
                    }}
                />
            )}

            <input type="hidden" {...inputProps} />

            {messages}

            <ErrorMessage />
        </>
    );
}
