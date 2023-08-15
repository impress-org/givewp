import {CurrencySwitcherSetting} from '@givewp/forms/types';
import {ChangeEvent, useMemo} from 'react';
import amountFormatter from '@givewp/forms/app/utilities/amountFormatter';

/**
 * @since 3.0.0
 */
const convertCurrencySettingsToOptions = (currencySettings: CurrencySwitcherSetting[]): CurrencyOption[] => {
    return currencySettings.map(({id}) => {
        const formatter = amountFormatter(id);
        const symbol = formatter.formatToParts().find(({type}) => type === 'currency').value;

        return {
            id,
            symbol,
        };
    });
};

/**
 * @since 3.0.0
 */
export type CurrencyOption = {
    id: string;
    symbol: string;
};

/**
 * Find the currency setting by currency id
 *
 * @since 3.0.0
 */
export const getCurrencySetting = (
    currency: string,
    currencySettings: CurrencySwitcherSetting[]
): CurrencySwitcherSetting | undefined => {
    return currencySettings.find(({id}) => id === currency);
};

/**
 * @since 3.0.0
 */
export const isBaseCurrency = (currencySetting: CurrencySwitcherSetting) => currencySetting.exchangeRate === 0;

/**
 * Calculate the amount based on the currency exchange rate, taking into account the from and to currency values
 *
 * @since 3.0.0
 */
export const calculateCurrencyAmount = (
    amount: number,
    fromCurrency: string,
    toCurrency: string,
    currencySettings: CurrencySwitcherSetting[]
): number => {
    const fromCurrencySetting = getCurrencySetting(fromCurrency, currencySettings);
    const toCurrencySetting = getCurrencySetting(toCurrency, currencySettings);

    // convert from currency to base amount by dividing by the current exchange rate
    // make sure to round the amount to avoid floating point issues
    if (fromCurrencySetting !== undefined && !isBaseCurrency(fromCurrencySetting)) {
        amount = Math.round(amount / fromCurrencySetting.exchangeRate);
    }

    // convert to next currency by multiplying by the next exchange rate
    if (toCurrencySetting !== undefined && !isBaseCurrency(toCurrencySetting)) {
        amount = amount * toCurrencySetting.exchangeRate;
    }

    return amount;
};

/**
 * @since 3.0.0
 */
type CurrencySwitcherProps = {
    defaultCurrency: string;
    currencySettings: CurrencySwitcherSetting[];
    onSelect?: (event: ChangeEvent<HTMLSelectElement>) => void;
};

/**
 * The currency select and static component
 *
 * @since 3.0.0
 */
function CurrencySwitcher({defaultCurrency, currencySettings, onSelect}: CurrencySwitcherProps) {
    const currencyOptions = useMemo(
        () => convertCurrencySettingsToOptions(currencySettings),
        [JSON.stringify(currencySettings)]
    );

    return (
        <span className="givewp-fields-amount__currency-select-container">
            <select
                className="givewp-fields-amount__currency-select"
                onChange={onSelect}
                defaultValue={defaultCurrency}
            >
                {currencyOptions.map((option) => {
                    return (
                        <option key={option.id} value={option.id}>
                            {option.id} {option.symbol}
                        </option>
                    );
                })}
            </select>
        </span>
    );
}
export default CurrencySwitcher;
