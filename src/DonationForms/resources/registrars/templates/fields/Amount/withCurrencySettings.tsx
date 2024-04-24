import {CurrencySwitcherSetting} from '@givewp/forms/types';
import {isBaseCurrency} from './CurrencySwitcher';

/**
 * Convert level amounts to the selected currency and fallback to original values if no currency setting is found or exchange rate is 0.
 *
 * @since 3.0.0
 */
export function getAmountLevelsWithCurrencySettings(
    levels: {label: string; value: number}[],
    currency: string,
    currencySettings: CurrencySwitcherSetting[]
) {
    const currencySetting = currencySettings.find(({id}) => id === currency);

    if (currencySetting === undefined || isBaseCurrency(currencySetting)) {
        return levels;
    }

    return levels.map((level) => {
        return {
            ...level,
            value: level.value * currencySetting.exchangeRate,
        };
    });
}

export function getDefaultAmountWithCurrencySettings(
    levels: {label: string; value: number}[],
    amount: number,
    currency: string,
    currencySettings: CurrencySwitcherSetting[]
) {
    const currencySetting = currencySettings.find(({id}) => id === currency);
    const defaultLevel = levels.find((level) => level.value === amount);

    if (currencySetting === undefined || isBaseCurrency(currencySetting) || defaultLevel === undefined) {
        return amount;
    }

    return defaultLevel.value * currencySetting.exchangeRate;
}
