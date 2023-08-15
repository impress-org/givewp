import {CurrencySwitcherSetting} from '@givewp/forms/types';
import {isBaseCurrency} from './CurrencySwitcher';

/**
 * Convert level amounts to the selected currency and fallback to original values if no currency setting is found or exchange rate is 0.
 *
 * @since 3.0.0
 */
export default function getAmountLevelsWithCurrencySettings(
    levels: number[],
    currency: string,
    currencySettings: CurrencySwitcherSetting[]
) {
    const currencySetting = currencySettings.find(({id}) => id === currency);

    if (currencySetting === undefined || isBaseCurrency(currencySetting)) {
        return levels;
    }

    return levels.map((levelAmount) => levelAmount * currencySetting.exchangeRate);
}